import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandLoading,
} from '@/components/ui/command';
import { Spinner } from '@/components/ui/spinner';
import type { MemberUser } from '@/types';

type PageProps = {
    users?: MemberUser[];
};

type Props = {
    onSelect: (user: MemberUser) => void;
};

export function UserCombobox({ onSelect }: Props) {
    const { users = [] } = usePage<PageProps>().props;

    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const [search, setSearch] = useState('');
    const [fetching, setFetching] = useState(false);

    useEffect(() => {
        return () => {
            if (timerRef.current !== null) {
                clearTimeout(timerRef.current);
            }

            router.cancelAll();
        };
    }, []);

    function handleSearch(value: string): void {
        setSearch(value);

        if (timerRef.current !== null) {
            clearTimeout(timerRef.current);
        }

        router.cancelAll();

        if (value.trim().length < 2) {
            setFetching(false);

            return;
        }

        setFetching(true);

        timerRef.current = setTimeout(() => {
            router.reload({
                only: ['users'],
                data: {
                    q_search: value.trim(),
                },
                // preserveState: true,
                // preserveScroll: true,
                onFinish() {
                    setFetching(false);
                },
            });
        }, 300);
    }

    function handleSelect(user: MemberUser): void {
        setSearch('');
        onSelect(user);
    }

    return (
        <Command shouldFilter={false} className="rounded-lg border" >
            <CommandInput
                value={search}
                onValueChange={handleSearch}
                placeholder="Rechercher un utilisateur"
            />

            {search.trim().length >= 2 && (
                <CommandList>
                    {fetching ? (
                        <CommandLoading>
                            <div className="flex items-center justify-center gap-2">
                                Recherche
                                <Spinner />
                            </div>
                        </CommandLoading>
                    ) : (
                        <>
                            <CommandEmpty>
                                Aucun utilisateur trouvé
                            </CommandEmpty>

                            <CommandGroup>
                                {users.map((user) => (
                                    <CommandItem
                                        key={user.id}
                                        value={`${user.name} ${user.email}`}
                                        onSelect={() => handleSelect(user)}
                                    >
                                        <div className="flex flex-col">
                                            <span>{user.name}</span>

                                            <span className="text-xs text-muted-foreground">
                                                {user.email}
                                            </span>
                                        </div>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </>
                    )}
                </CommandList>
            )}
        </Command>
    );
}