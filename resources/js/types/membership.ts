import type { MembershipStatus } from './generated/types/App/Enums';

export type UserOption = {
    id: number;
    name: string;
    email: string;
};

export interface MembersipUser extends UserOption {
    roles: {
        id: number;
        name: string;
    }[];
}

export type Membership = {
    id: number;
    tontine_id: number;
    tontine_slug: string;
    member_number: string;
    status: MembershipStatus;
    status_label: string;
    role: {
        name: string;
        label: string;
    };
    user: MembersipUser;
};
