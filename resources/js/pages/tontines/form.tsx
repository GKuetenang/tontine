import { Form } from '@inertiajs/react';
import { SaveIcon } from 'lucide-react';
import { FormField } from '@/components/form-field';
import { TopActions } from '@/components/top-actions';
import { Button } from '@/components/ui/button';
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
        ? tontines.update.form({ tontine: tontine.id })
        : tontines.store.form();

    return (
        <Form {...action}>
            {({ errors, processing, progress }) => (
                <>
                    <div className="space-y-4">
                        <FormField
                            label="Image"
                            help="Formats : jpg,jpeg,png ou webp"
                            error={errors['image']}
                        >
                            <ImageInput
                                className="mt-3 aspect-square w-40!"
                                name="image"
                                aria-invalid={!!errors['image']}
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
                            error={errors['slug']}
                            label="Slug"
                            htmlFor="slug"
                            help="Identifiant unique utilisé dans l’adresse URL de la tontine. Utilisez uniquement des lettres minuscules, des chiffres et des tirets."
                        >
                            <Input
                                id="slug"
                                name="slug"
                                defaultValue={tontine.slug}
                                aria-invalid={!!errors['slug']}
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
                                defaultValue={tontine.description}
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
    );
});
