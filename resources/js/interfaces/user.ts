export interface Role {
    id: number;
    name: string;
}

export interface User {
    id: number;
    name: string;
    email: string | null;
    username: string;
    role: string;
    email_verified_at?: string;
}

export interface UserListItem extends Omit<User, 'role'> {
    role_id: number;
    role: Role;
    is_active: boolean;
}
