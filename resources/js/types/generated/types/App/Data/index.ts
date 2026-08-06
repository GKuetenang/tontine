export type Session = {
    name: string;
    slug: string;
    id: undefined | number;
    description: undefined | string | null;
    start_at: undefined | undefined | null;
    end_at: undefined | undefined | null;
    is_active: undefined | boolean;
    is_closed: undefined | boolean;
    activated_at: undefined | undefined | null;
    closed_at: undefined | undefined | null;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
};
export type Tontine = {
    name: string;
    slug: undefined | string;
    member_number_prefix: string;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
    image: undefined | undefined | string;
    image_file: File;
    can: undefined | TontineAbilitiesData;
    id: undefined | number;
    members_count: undefined | number;
    sessions_count: undefined | number;
    currency: undefined | string;
    is_active: undefined | boolean;
    is_public: undefined | boolean;
    is_verified: undefined | boolean;
    description: string | null;
};
export type TontineAbilitiesData = {
    view: boolean;
    update: boolean;
    delete: boolean;
    view_memberships: boolean;
};
