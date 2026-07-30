import { CollectionPagination } from '@/components/collection-pagination';
import Heading from '@/components/heading';
import { MembershipStatusBadge } from '@/components/membership-status-badge';
import { SelectOption } from '@/components/select-with-items';
import { TopActions } from '@/components/top-actions';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { withAppLayout } from '@/layouts/app-layout';
import memberships from '@/routes/memberships';
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, Membership, PaginatedCollection } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';
import { EditIcon, PlusIcon, TrashIcon } from 'lucide-react';
import { toast } from 'sonner';
import { EditMemberForm } from './form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
    {
        title: 'Membres',
        href: '#',
    },
];

export type ResultTontine = {
    id: number;
    name: string;
    slug: string;
}

type Props = {
    collection: PaginatedCollection<Membership>;
    q: string | null;
    tontine: ResultTontine;
    roles: SelectOption[];
    membership: Membership;
    statuses: SelectOption[];
};

export default withAppLayout(breadcrumbs, ({ collection, q, tontine, roles, membership, statuses }: Props) => {
    console.log(collection);

    return (
        <>
            <Head title='Tous les membres' />
            <Heading
                title='Tous les membres'
            />
            <div className="space-y-4">
                <TopActions>
                    <Form
                        {...memberships.index.form({
                            tontine: tontine.id
                        })}
                        className="flex items-center gap-1"
                    >
                        <Input
                            autoFocus
                            defaultValue={q ?? ''}
                            placeholder="Rechercher un membre"
                            name="q"
                        />
                        <Button>Rechercher</Button>
                    </Form>
                </TopActions>
                <Card className='bg-background pt-0'>
                    <CardHeader className='border-b py-4'>
                        <EditMemberForm
                            membership={membership}
                            roles={roles}
                            tontine={tontine}
                            trigger={
                                <Button
                                    type='button'
                                    variant="outline"
                                    className="w-fit"
                                >
                                    <PlusIcon />
                                    Ajouter un membre
                                </Button>
                            }
                        />
                    </CardHeader>
                    <CardContent className='px-0'>
                        <Table className='border-spacing-4'>
                            <TableHeader>
                                <TableRow className='[&>th:first-child]:pl-6 [&>th:last-child]:pr-6'>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Numéro</TableHead>
                                    <TableHead>Nom</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead className="text-end">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {collection.data.map((item) => (
                                    <TableRow key={item.id} className='[&>td:first-child]:pl-6 [&>td:last-child]:pr-6'>
                                        <TableCell>{item.id}</TableCell>
                                        <TableCell>
                                            {item.member_number}
                                        </TableCell>
                                        <TableCell>
                                            {item.user.name}
                                        </TableCell>
                                        <TableCell>
                                            {item.user.email}
                                        </TableCell>
                                        <TableCell>
                                            {item.role.label}
                                        </TableCell>
                                        <TableCell>
                                            <MembershipStatusBadge status={item.status} />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center justify-end gap-2">
                                                <EditMemberForm
                                                    membership={item}
                                                    roles={roles}
                                                    tontine={tontine}
                                                    statuses={statuses}
                                                    trigger={
                                                        <Button
                                                            size="icon"
                                                            variant="outline"
                                                        >
                                                            <EditIcon size={16} />
                                                        </Button>
                                                    }
                                                />

                                                <Button
                                                    asChild
                                                    size="icon"
                                                    variant="destructive-outline"
                                                >
                                                    <Link
                                                        href={memberships.destroy({
                                                            tontine: item.tontine_id,
                                                            membership: item.id,
                                                        })}
                                                        onBefore={() =>
                                                            confirm(
                                                                'Voulez-vous vraiment supprimer ce membre?',
                                                            )
                                                        }
                                                        onError={(errors) => {
                                                            const firstError = Object.values(errors)[0];
                                                            toast.error(firstError);
                                                        }}
                                                    >
                                                        <TrashIcon size={16} />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        <CollectionPagination className='px-6 pt-6' collection={collection} />
                    </CardContent>
                </Card>
            </div>
        </>
    );
});

// withAppLayout.layout = {

// }
