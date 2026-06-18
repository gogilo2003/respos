import { User as BaseUser } from '../interfaces/user';

export interface User extends BaseUser {}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
