import { Form, Head } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import { FormField } from '@/components/form-field';
import Heading from '@/components/heading';
import { TopActions } from '@/components/top-actions';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ImageInput } from '@/components/ui/image-input';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { withAppLayout } from '@/layouts/app-layout';
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
                        {({ errors, processing, progress }) => (
                            <>
                                <div className="space-y-4">
                                    <FormField
                                        label="Image"
                                        help="Formats : jpg,jpeg,png ou webp"
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
                                    </FormField>
                                    <FormField
                                        error={errors['name']}
                                        label="Nom"
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
                                        label="Devise"
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
                                        label="Préfixe du numéro de membre"
                                        htmlFor="member_number_prefix"
                                        required
                                        help="Le préfixe du numéro de membre est utilisé pour identifier les membres de la réunion."
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
                                            label="Taux d’intérêt des prêts (%)"
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
                                            label="Échéance des prêts (mois)"
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
                                        label="Montant de réunion par défaut"
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
                                        label="Description"
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
                                </div>

                                <TopActions>
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
                                        Enregistrer
                                    </Button>
                                </TopActions>
                            </>
                        )}
                    </Form>
                </CardContent>
            </Card>
        </>
    );
});
