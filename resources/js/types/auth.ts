export type User = {
    id: number;
    name: string;
    email: string;
    phone: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    address: string | null;
    postal_code: string | null;
    city: string | null;
    country: string | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    isCoordinator: boolean;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */
