export type Tontine = {
    name: string;
    slug: string;
    member_number_prefix: string;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
    image: undefined | undefined | string;
    image_file: undefined | undefined;
    can: undefined | TontineAbilitiesData;
    id: undefined | number;
    members_count: undefined | number;
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
