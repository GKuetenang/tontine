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
import tontines from '@/routes/tontines';
import type { BreadcrumbItem, Tontine } from '@/types';

type Props = {
    tontine: Tontine;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tontines',
        href: tontines.index().url,
    },
    {
        title: 'Editer',
        href: '#',
    },
];

export default withAppLayout<Props>(breadcrumbs, ({ tontine }) => {
    const action = tontine.id
        ? tontines.update.form({ tontine: tontine.slug! })
        : tontines.store.form();
    const title = tontine.id ? 'Editer une tontine' : 'Ajouter une tontine';

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
                                            defaultValue={tontine.image}
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
                                            defaultValue={tontine.name}
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
                                            defaultValue={tontine.currency}
                                            aria-invalid={!!errors['currency']}
                                        />
                                    </FormField>

                                    <FormField
                                        error={errors['member_number_prefix']}
                                        label="Préfixe du numéro de membre"
                                        htmlFor="member_number_prefix"
                                        required
                                        help="Le préfixe du numéro de membre est utilisé pour identifier les membres de la tontine."
                                    >
                                        <Input
                                            id="member_number_prefix"
                                            name="member_number_prefix"
                                            defaultValue={
                                                tontine.member_number_prefix
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
                                                    tontine.default_loan_interest_rate
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
                                                    tontine.default_loan_term_months
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
                                        label="Montant de tontine par defaut"
                                        htmlFor="default_contribution_amount"
                                        optional
                                    >
                                        <Input
                                            id="default_contribution_amount"
                                            name="default_contribution_amount"
                                            defaultValue={
                                                tontine.default_contribution_amount ??
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
                                                tontine.description ?? ''
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
