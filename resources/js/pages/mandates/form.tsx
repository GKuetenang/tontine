import { Form } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { SaveIcon } from 'lucide-react';
import { useState } from 'react';

import { AppDateTimePicker } from '@/components/app-datetime-picker';
import { FormField } from '@/components/form-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { useTranslation } from '@/hooks/use-translation';
import mandates from '@/routes/groups/mandates';
import type { Group } from '@/types';

import type { MandateItem } from './index';
import type { ReactElement } from 'react';

export function MandateForm({
    group,
    mandate,
    trigger,
}: {
    group: Group;
    mandate?: MandateItem;
    trigger: ReactElement;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [startsAt, setStartsAt] = useState<Date | undefined>(
        mandate ? parseISO(mandate.starts_at) : undefined,
    );
    const [endsAt, setEndsAt] = useState<Date | undefined>(
        mandate ? parseISO(mandate.ends_at) : undefined,
    );
    const action = mandate
        ? mandates.update.form({ group: group.slug!, mandate: mandate.id })
        : mandates.store.form({ group: group.slug! });

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <Form {...action} onSuccess={() => setOpen(false)}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <DialogHeader>
                                <DialogTitle>
                                    {mandate
                                        ? 'Modifier le mandat'
                                        : 'Créer un mandat'}
                                </DialogTitle>
                                <DialogDescription>
                                    {t(
                                        'Définissez une période de gouvernance indépendante des sessions.',
                                    )}
                                </DialogDescription>
                            </DialogHeader>
                            <FormField
                                label={t('Nom')}
                                htmlFor="name"
                                error={errors.name}
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={mandate?.name}
                                    required
                                />
                            </FormField>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField
                                    label={t('Début')}
                                    htmlFor="starts_at"
                                    error={errors.starts_at}
                                >
                                    <input
                                        type="hidden"
                                        name="starts_at"
                                        value={
                                            startsAt
                                                ? format(startsAt, 'yyyy-MM-dd')
                                                : ''
                                        }
                                    />
                                    <AppDateTimePicker
                                        granularity="day"
                                        value={startsAt}
                                        onChange={setStartsAt}
                                        placeholder={t(
                                            'Choisir la date de début',
                                        )}
                                    />
                                </FormField>
                                <FormField
                                    label={t('Fin')}
                                    htmlFor="ends_at"
                                    error={errors.ends_at}
                                >
                                    <input
                                        type="hidden"
                                        name="ends_at"
                                        value={
                                            endsAt
                                                ? format(endsAt, 'yyyy-MM-dd')
                                                : ''
                                        }
                                    />
                                    <AppDateTimePicker
                                        granularity="day"
                                        value={endsAt}
                                        onChange={setEndsAt}
                                        placeholder={t(
                                            'Choisir la date de fin',
                                        )}
                                    />
                                </FormField>
                            </div>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">
                                        {t('Annuler')}
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {processing ? <Spinner /> : <SaveIcon />}{' '}
                                    {t('Enregistrer')}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
