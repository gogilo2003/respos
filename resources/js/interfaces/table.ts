export interface RestaurantTable {
    id: number;
    table_number: string;
    capacity: number;
    location: string | null;
    status: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    qr_code?: {
        id: number;
        payload: string;
        image_path: string | null;
        generated_at: string;
        regenerated_at: string | null;
    } | null;
}

export interface TableSession {
    id: number;
    table_id: number;
    session_token: string;
    opened_by: number | null;
    open_source: string;
    status: string;
    customer_count: number | null;
    notes: string | null;
    token_expires_at: string;
    opened_at: string;
    closed_at: string | null;
    closed_by: number | null;
    close_reason: string | null;
    created_at: string;
    updated_at: string;
    table?: RestaurantTable;
}