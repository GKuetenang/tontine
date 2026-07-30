import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { PaginatedCollection } from '@/types';
import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = { collection: PaginatedCollection<unknown>, className?: string };

export function CollectionPagination({ collection, className }: Props) {
    return (
        <div className={cn("flex items-center justify-between", className)}>
            <div className="hidden flex-1 text-sm text-muted-foreground lg:flex">
                Page {collection.current_page} sur {collection.last_page}
            </div>
            <nav role="navigation" aria-label="Pagination">
                <ul className="flex items-center gap-1">
                    {collection.links.map((link, index) => (
                        <li key={index}>
                            <Button
                                asChild
                                disabled={link.url === null}
                                aria-current={link.active ? 'page' : undefined}
                                data-active={link.active}
                                variant={link.active ? 'outline' : 'secondary'}
                                size="icon"
                            >
                                <Link href={link.url ?? '#'}>
                                    {label(
                                        link.label,
                                        index,
                                        collection.links.length,
                                    )}
                                </Link>
                            </Button>
                        </li>
                    ))}
                </ul>
            </nav>
        </div>
    );
}

function label(s: string, index: number, count: number): ReactNode {
    if (index === 0) {
        return <ChevronLeftIcon />;
    }

    if (index === count - 1) {
        return <ChevronRightIcon />;
    }

    return s;
}
