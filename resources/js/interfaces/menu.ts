export interface MenuCategory {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
    created_at?: string;
    menu_items_count?: number;
}

export interface ModifierOption {
    name: string;
    price: number;
}

export interface ModifierGroup {
    name: string;
    required: boolean;
    options: ModifierOption[];
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
    modifier_groups: ModifierGroup[] | null;
    is_available: boolean;
    is_featured: boolean;
    sort_order: number;
    created_at?: string;
    updated_at?: string;
    category?: MenuCategory;
}
