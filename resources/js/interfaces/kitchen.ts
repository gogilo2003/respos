export interface KitchenOrderItem {
    orderItemId: number;
    name: string | null;
    quantity: number;
    status: string;
    notes?: string;
    allergies?: string;
    specialInstructions?: string;
}

export interface KitchenOrder {
    orderNumber: number | string;
    table: string;
    customer?: string;
    orderTime: string;
    waitingDuration: string;
    status?: string;
    items: KitchenOrderItem[];
}

export interface KitchenStatistics {
    pendingOrders: number;
    preparing: number;
    ready: number;
    completedToday: number;
}

export interface KitchenStation {
    id: number | string;
    name: string;
    type: string;
    isActive: boolean;
    currentUser?: string;
}
