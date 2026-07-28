export type PaginatedCollection<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    path: string;
    per_page: number;
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
        url: string | null;
        label: string;
        active: boolean;
    }[];
    from: number;
    // current_page: number;
    // last_page: number;
    // path: string;
    // per_page: number;
    // to: number;
    // total: number;
    // links: {
    //     url: string | null;
    //     label: string;
    //     active: boolean;
    // }[];
    // meta: {
    //     from: number;
    //     current_page: number;
    //     last_page: number;
    //     path: string;
    //     per_page: number;
    //     to: number;
    //     total: number;
    //     links: {
    //         url: string | null;
    //         label: string;
    //         active: boolean;
    //     }[];
    // };
};
