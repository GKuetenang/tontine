import {
    ContributionStatus,
    MeetingStatus,
    AttendanceStatus,
    MembershipStatus,
    PayoutStatus,
    SessionStatus,
    DrawAllocationMode,
} from '../Enums';
export type Contribution = {
    id: number;
    amount_due: number;
    amount_paid: number;
    remaining_amount: number;
    status: ContributionStatus;
    session_participant: undefined | SessionParticipant;
    created_at: undefined;
    updated_at: undefined;
};
export type Draw = {
    id: number;
    entries: undefined | Array<any>;
    confirmed_at: undefined | null;
    created_at: undefined;
    updated_at: undefined;
};
export type DrawEntry = {
    id: number;
    position: number;
    entry_number: number;
    session_participant: undefined | SessionParticipant;
    expected_meeting: ExpectedDrawMeeting | null;
    created_at: undefined;
    updated_at: undefined;
};
export type ExpectedDrawMeeting = {
    id: number;
    number: number;
    slug: string;
    scheduled_at: undefined;
};
export type Meeting = {
    id: number;
    number: number;
    title: string;
    slug: string;
    description: string | null;
    location: string | null;
    status: MeetingStatus;
    scheduled_at: undefined;
    agenda_items: undefined | Array<any>;
    opened_at: undefined | undefined | null;
    closed_at: undefined | undefined | null;
    attendances_count: undefined | number;
    contributions_count: undefined | number;
    attendances: undefined | Array<any>;
    contributions: undefined | Array<any>;
    notes: undefined | Array<any>;
    decisions: undefined | Array<any>;
    payouts: undefined | Array<any>;
    created_at: undefined;
    updated_at: undefined;
};
export type MeetingAgendaItem = {
    id: number;
    title: string;
    description: string | null;
    position: number;
    created_at: undefined;
    updated_at: undefined;
};
export type MeetingAttendance = {
    id: number;
    status: AttendanceStatus;
    checked_in_at: undefined | null;
    note: string | null;
    session_participant: undefined | SessionParticipant;
    created_at: undefined;
    updated_at: undefined;
};
export type MeetingDecision = {
    id: number;
    title: string;
    description: string | null;
    agenda_item: undefined | MeetingAgendaItem | null;
    creator: undefined | MemberUser | null;
    created_at: undefined;
    updated_at: undefined;
};
export type MeetingNote = {
    id: number;
    content: string;
    agenda_item: undefined | MeetingAgendaItem | null;
    creator: undefined | MemberUser | null;
    created_at: undefined;
    updated_at: undefined;
};
export type MeetingSummaryData = {
    id: number;
    slug: string;
    name: string;
    scheduled_at: undefined;
};
export type MemberUser = {
    id: number;
    name: string;
    email: string;
    username: string;
};
export type Membership = {
    id: number;
    member_number: string;
    status: MembershipStatus;
    verified_at: undefined | undefined | null;
    joined_at: undefined | undefined | null;
    left_at: undefined | undefined | null;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
    deleted_at: undefined | undefined | null;
    user: undefined | MemberUser;
    inviter: undefined | MemberUser | null;
    creator: undefined | MemberUser | null;
    role: undefined | MembershipRole | null;
};
export type MembershipRole = {
    id: number;
    name: string;
    label: string;
};
export type Payout = {
    id: number;
    amount: number;
    status: PayoutStatus;
    paid_at: undefined | null;
    draw_entry: undefined | DrawEntry;
    creator: undefined | MemberUser | null;
    created_at: undefined;
    updated_at: undefined;
};
export type Session = {
    name: string;
    slug: string;
    id: undefined | number;
    description: undefined | string | null;
    start_at: undefined | undefined | null;
    end_at: undefined | undefined | null;
    default_contribution_amount: undefined | number | null;
    beneficiaries_per_meeting: number;
    status: undefined | SessionStatus;
    participants_count: undefined | number;
    meetings_count: undefined | number;
    draw_allocation_mode: undefined | DrawAllocationMode;
    draw_allocation_mode_label: undefined | string;
    activated_at: undefined | undefined | null;
    closed_at: undefined | undefined | null;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
};
export type SessionParticipant = {
    id: number;
    contribution_amount: number;
    draw_entries_count: number;
    is_active: boolean;
    joined_at: undefined | undefined | null;
    left_at: undefined | undefined | null;
    created_at: undefined | undefined;
    updated_at: undefined | undefined;
    membership: undefined | Membership;
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
    default_contribution_amount: undefined | number | null;
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
