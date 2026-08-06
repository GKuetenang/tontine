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
import { Form, Head } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';

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
    const title = tontine.id ? "Editer une tontine" : "Ajouter une tontine"

    return (
        <>
            <Head title={title} />
            <Heading
                title={title}
            />
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
                                            aria-invalid={!!errors['image_file']}
                                            defaultValue={tontine.image}
                                            progress={progress?.progress}
                                        />
                                    </FormField>
                                    <FormField
                                        error={errors['name']}
                                        label="Nom"
                                        htmlFor="name"
                                    >
                                        <Input
                                            id="name"
                                            name="name"
                                            defaultValue={tontine.name}
                                            aria-invalid={!!errors['name']}
                                        />
                                    </FormField>
                                    <FormField
                                        error={errors['member_number_prefix']}
                                        label="Préfixe du numéro de membre"
                                        htmlFor="member_number_prefix"
                                        help="Le préfixe du numéro de membre est utilisé pour identifier les membres de la tontine."
                                    >
                                        <Input
                                            id="member_number_prefix"
                                            name="member_number_prefix"
                                            defaultValue={tontine.member_number_prefix}
                                            aria-invalid={!!errors['member_number_prefix']}
                                        />
                                    </FormField>
                                    <FormField
                                        error={errors['description']}
                                        label="Description"
                                        htmlFor="description"
                                    >
                                        <Textarea
                                            id="description"
                                            name="description"
                                            defaultValue={tontine.description ?? ''}
                                            aria-invalid={!!errors['description']}
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
                                        {processing ? <Spinner /> : <SaveIcon />}
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
