export interface MenuCategory {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    menu_items_count?: number;
}

export interface MenuItem {
    id: number;
    category_id: number;
    name: string;
    description: string | null;
    base_price: string | number;
    tax_inclusive: boolean;
    prep_time_min: number;
    image_url: string | null;
    modifier_groups: any[] | null;
    is_available: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    category?: MenuCategory;
}