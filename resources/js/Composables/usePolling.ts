import { onMounted, onUnmounted, ref } from 'vue';

export interface NotificationItem {
    id: number;
    target_role: string;
    event_type: string;
    session_id?: number;
    payload?: any;
    created_at: string;
    time_ago: string;
}

export interface PollingResponse {
    ok: boolean;
    summary: Record<string, number>;
    notifications: NotificationItem[];
    unread_count: number;
    server_time: string;
}

export function usePolling(intervalMs: number = 8000, onUpdate?: (data: PollingResponse) => void) {
    const unreadCount = ref<number>(0);
    const notifications = ref<NotificationItem[]>([]);
    const summary = ref<Record<string, number>>({});
    const lastPollTime = ref<string | null>(null);
    const isPolling = ref<boolean>(false);
    let timerId: number | null = null;

    const poll = async () => {
        if (isPolling.value) return;
        isPolling.value = true;

        try {
            const url = new URL(route('api.polling.updates'), window.location.origin);
            if (lastPollTime.value) {
                url.searchParams.append('since', lastPollTime.value);
            }

            const res = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (res.ok) {
                const data: PollingResponse = await res.json();
                if (data.ok) {
                    unreadCount.value = data.unread_count;
                    notifications.value = data.notifications;
                    summary.value = data.summary;
                    lastPollTime.value = data.server_time;

                    if (onUpdate) {
                        onUpdate(data);
                    }
                }
            }
        } catch (err) {
            console.warn('Polling request error:', err);
        } finally {
            isPolling.value = false;
        }
    };

    const markRead = async (id: number) => {
        try {
            await fetch(route('api.polling.mark-read', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
            });
            notifications.value = notifications.value.filter((n) => n.id !== id);
            if (unreadCount.value > 0) {
                unreadCount.value--;
            }
        } catch (err) {
            console.error('Mark read error:', err);
        }
    };

    const startPolling = () => {
        stopPolling();
        poll();
        timerId = window.setInterval(poll, intervalMs);
    };

    const stopPolling = () => {
        if (timerId !== null) {
            clearInterval(timerId);
            timerId = null;
        }
    };

    onMounted(() => {
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        unreadCount,
        notifications,
        summary,
        poll,
        markRead,
        startPolling,
        stopPolling,
    };
}
