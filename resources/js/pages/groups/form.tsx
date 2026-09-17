import { Form, Head } from '@inertiajs/react';
import { format } from 'date-fns';
import { SaveIcon } from 'lucide-react';
import { useState } from 'react';

import { FormField } from '@/components/form-field';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DateTimePicker } from '@/components/ui/datetime-picker';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from '@/hooks/use-translation';
import { withAppLayout } from '@/layouts/app-layout';
import { getDateFnsLocale } from '@/lib';
import groups from '@/routes/groups';
import type { BreadcrumbItem, Group } from '@/types';

type Props = {
    group: Group;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Réunions',
        href: groups.index().url,
    },
    {
        title: 'Editer',
        href: '#',
    },
];

export default withAppLayout<Props>(breadcrumbs, ({ group }) => {
    const { t, locale } = useTranslation();
    const [mandateStartsAt, setMandateStartsAt] = useState<Date>();
    const [mandateEndsAt, setMandateEndsAt] = useState<Date>();
    const action = group.id
        ? groups.update.form({ group: group.slug! })
        : groups.store.form();
    const title = group.id ? 'Editer une réunion' : 'Ajouter une réunion';

    return (
        <>
            <Head title={title} />
            <Heading title={title} />
            <Card>
                <CardContent>
                    <Form {...action}>
                        {({ errors, processing }) => (
                            <>
                                <div className="space-y-4">
                                    {/* <FormField
                                        label={t('Image')}
                                        help={t(
                                            'Formats : jpg,jpeg,png ou webp',
                                        )}
                                        error={errors['image_file']}
                                    >
                                        <ImageInput
                                            className="mt-3 aspect-square w-40!"
                                            name="image_file"
                                            aria-invalid={
                                                !!errors['image_file']
                                            }
                                            defaultValue={group.image}
                                            progress={progress?.progress}
                                        />
                                    </FormField> */}
                                    <FormField
                                        error={errors['name']}
                                        label={t('Nom')}
                                        htmlFor="name"
                                        required
                                    >
                                        <Input
                                            id="name"
                                            name="name"
                                            defaultValue={group.name}
                                            aria-invalid={!!errors['name']}
                                        />
                                    </FormField>
                                    <FormField
                                        error={errors['currency']}
                                        label={t('Devise')}
                                        htmlFor="currency"
                                    >
                                        <Input
                                            id="currency"
                                            name="currency"
                                            defaultValue={group.currency}
                                            aria-invalid={!!errors['currency']}
                                        />
                                    </FormField>

                                    <FormField
                                        error={errors['member_number_prefix']}
                                        label={t('Préfixe du numéro de membre')}
                                        htmlFor="member_number_prefix"
                                        required
                                        help={t(
                                            'Le préfixe du numéro de membre est utilisé pour identifier les membres de la réunion.',
                                        )}
                                    >
                                        <Input
                                            id="member_number_prefix"
                                            name="member_number_prefix"
                                            defaultValue={
                                                group.member_number_prefix
                                            }
                                            aria-invalid={
                                                !!errors['member_number_prefix']
                                            }
                                        />
                                    </FormField>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <FormField
                                            error={
                                                errors[
                                                'default_loan_interest_rate'
                                                ]
                                            }
                                            label={t(
                                                'Taux d’intérêt des prêts (%)',
                                            )}
                                            htmlFor="default_loan_interest_rate"
                                            required
                                        >
                                            <Input
                                                id="default_loan_interest_rate"
                                                name="default_loan_interest_rate"
                                                inputMode="decimal"
                                                defaultValue={
                                                    group.default_loan_interest_rate
                                                }
                                                aria-invalid={
                                                    !!errors[
                                                    'default_loan_interest_rate'
                                                    ]
                                                }
                                            />
                                        </FormField>
                                        <FormField
                                            error={
                                                errors[
                                                'default_loan_term_months'
                                                ]
                                            }
                                            label={t(
                                                'Échéance des prêts (mois)',
                                            )}
                                            htmlFor="default_loan_term_months"
                                            required
                                        >
                                            <Input
                                                id="default_loan_term_months"
                                                name="default_loan_term_months"
                                                type="number"
                                                min={1}
                                                max={120}
                                                defaultValue={
                                                    group.default_loan_term_months
                                                }
                                                aria-invalid={
                                                    !!errors[
                                                    'default_loan_term_months'
                                                    ]
                                                }
                                            />
                                        </FormField>
                                    </div>
                                    <FormField
                                        error={
                                            errors[
                                            'default_contribution_amount'
                                            ]
                                        }
                                        label={t(
                                            'Montant de réunion par défaut',
                                        )}
                                        htmlFor="default_contribution_amount"
                                        optional
                                    >
                                        <Input
                                            id="default_contribution_amount"
                                            name="default_contribution_amount"
                                            defaultValue={
                                                group.default_contribution_amount ??
                                                undefined
                                            }
                                            aria-invalid={
                                                !!errors[
                                                'default_contribution_amount'
                                                ]
                                            }
                                        />
                                    </FormField>
                                    <FormField
                                        error={errors['description']}
                                        label={t('Description')}
                                        htmlFor="description"
                                        optional
                                    >
                                        <Textarea
                                            id="description"
                                            name="description"
                                            defaultValue={
                                                group.description ?? ''
                                            }
                                            aria-invalid={
                                                !!errors['description']
                                            }
                                        />
                                    </FormField>
                                    {!group.id && (
                                        <div className="space-y-4 rounded-lg border p-4">
                                            <div>
                                                <h2 className="font-medium">
                                                    {t('Premier mandat')}
                                                </h2>
                                                <p className="text-sm text-muted-foreground">
                                                    {t(
                                                        'Vous serez administrateur pendant cette période. Les responsabilités suivantes seront gérées dans le module Mandats.',
                                                    )}
                                                </p>
                                            </div>
                                            <FormField
                                                error={
                                                    errors[
                                                    'initial_mandate_name'
                                                    ]
                                                }
                                                label={t('Nom du mandat')}
                                                htmlFor="initial_mandate_name"
                                                required
                                            >
                                                <Input
                                                    id="initial_mandate_name"
                                                    name="initial_mandate_name"
                                                    placeholder={t(
                                                        'Mandat 2026–2028',
                                                    )}
                                                />
                                            </FormField>
                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <FormField
                                                    error={
                                                        errors[
                                                        'initial_mandate_starts_at'
                                                        ]
                                                    }
                                                    label={t('Début du mandat')}
                                                    htmlFor="initial_mandate_starts_at"
                                                    required
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="initial_mandate_starts_at"
                                                        value={
                                                            mandateStartsAt
                                                                ? format(
                                                                    mandateStartsAt,
                                                                    'yyyy-MM-dd',
                                                                )
                                                                : ''
                                                        }
                                                    />
                                                    <DateTimePicker
                                                        weekStartsOn={1}
                                                        locale={getDateFnsLocale(locale)}
                                                        granularity="minute"
                                                        value={mandateStartsAt}
                                                        onChange={
                                                            setMandateStartsAt
                                                        }
                                                        placeholder={t(
                                                            'Choisir la date de début',
                                                        )}
                                                    />
                                                </FormField>
                                                <FormField
                                                    error={
                                                        errors[
                                                        'initial_mandate_ends_at'
                                                        ]
                                                    }
                                                    label={t('Fin du mandat')}
                                                    htmlFor="initial_mandate_ends_at"
                                                    required
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="initial_mandate_ends_at"
                                                        value={
                                                            mandateEndsAt
                                                                ? format(
                                                                    mandateEndsAt,
                                                                    'yyyy-MM-dd',
                                                                )
                                                                : ''
                                                        }
                                                    />
                                                    <DateTimePicker
                                                        weekStartsOn={1}
                                                        locale={getDateFnsLocale(locale)}
                                                        granularity="minute"
                                                        value={mandateEndsAt}
                                                        onChange={
                                                            setMandateEndsAt
                                                        }
                                                        placeholder={t(
                                                            'Choisir la date de fin',
                                                        )}
                                                    />
                                                </FormField>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="mt-4">
                                    <Button
                                        type="submit"
                                        tabIndex={4}
                                        disabled={processing}
                                        data-test="login-button"
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <SaveIcon />
                                        )}
                                        {t('Enregistrer')}
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </CardContent>
            </Card>
        </>
    );
});
