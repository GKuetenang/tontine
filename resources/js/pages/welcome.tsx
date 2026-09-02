import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRightIcon, CheckIcon, HandCoinsIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { FeatureGrid } from '@/components/welcome/feature-grid';
import { ProductPreview } from '@/components/welcome/product-preview';
import { WelcomeHeader } from '@/components/welcome/welcome-header';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth, name } = usePage().props;
    const primaryRoute = auth.user ? dashboard() : register();

    return (
        <>
            <Head title="Gérez votre réunion simplement">
                <meta
                    name="description"
                    content="Une plateforme complète pour gérer les membres, assises et finances de votre réunion."
                />
            </Head>
            <div className="min-h-screen bg-background text-foreground">
                <WelcomeHeader authenticated={Boolean(auth.user)} />

                <main>
                    <section className="relative overflow-hidden py-16 sm:py-24 lg:py-28">
                        <div className="absolute inset-x-0 top-0 -z-10 h-[560px] bg-gradient-to-b from-primary/8 via-primary/3 to-transparent" />
                        <div className="mx-auto grid max-w-7xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                            <div className="max-w-xl">
                                <Badge
                                    variant="secondary"
                                    className="mb-6 rounded-full px-3 py-1"
                                >
                                    <HandCoinsIcon /> La gestion collective,
                                    simplifiée
                                </Badge>
                                <h1 className="text-4xl leading-tight font-semibold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                                    Gérez votre réunion avec{' '}
                                    <span className="text-primary">
                                        clarté et confiance.
                                    </span>
                                </h1>
                                <p className="mt-6 max-w-lg text-lg leading-8 text-muted-foreground">
                                    Membres, assises, cotisations, prêts et
                                    rapports : centralisez toute la vie de votre
                                    association dans un espace simple, sécurisé
                                    et traçable.
                                </p>
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <Button asChild size="lg">
                                        <Link href={primaryRoute}>
                                            {auth.user
                                                ? 'Ouvrir mon tableau de bord'
                                                : 'Créer ma première réunion'}
                                            <ArrowRightIcon />
                                        </Link>
                                    </Button>
                                    {!auth.user && (
                                        <Button
                                            asChild
                                            size="lg"
                                            variant="outline"
                                        >
                                            <Link href={login()}>
                                                J’ai déjà un compte
                                            </Link>
                                        </Button>
                                    )}
                                </div>
                                <div className="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-muted-foreground">
                                    {[
                                        'Suivi financier fiable',
                                        'Rôles personnalisables',
                                        'Rapports centralisés',
                                    ].map((item) => (
                                        <span
                                            key={item}
                                            className="flex items-center gap-2"
                                        >
                                            <span className="grid size-5 place-items-center rounded-full bg-emerald-500/10 text-emerald-600">
                                                <CheckIcon className="size-3" />
                                            </span>
                                            {item}
                                        </span>
                                    ))}
                                </div>
                            </div>
                            <ProductPreview />
                        </div>
                    </section>

                    <section className="pb-20 sm:pb-24">
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="grid gap-4 sm:grid-cols-3">
                                {[
                                    [
                                        'Une seule source',
                                        'Toutes les opérations et décisions sont conservées dans un espace commun.',
                                    ],
                                    [
                                        'Des responsabilités claires',
                                        'Chaque membre accède uniquement aux fonctions liées à son rôle.',
                                    ],
                                    [
                                        'Une vision immédiate',
                                        'Les échéances, assises et mouvements récents restent visibles au bon moment.',
                                    ],
                                ].map(([title, description], index) => (
                                    <Card
                                        key={title}
                                        className="border-border/70 shadow-none"
                                    >
                                        <CardContent>
                                            <span className="text-sm font-semibold text-primary">
                                                0{index + 1}
                                            </span>
                                            <h2 className="mt-3 font-semibold">
                                                {title}
                                            </h2>
                                            <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                                {description}
                                            </p>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </div>
                    </section>

                    <FeatureGrid />

                    <section className="py-20 sm:py-24">
                        <div className="mx-auto max-w-5xl px-4 sm:px-6">
                            <Card className="overflow-hidden border-primary/20 bg-primary text-primary-foreground shadow-xl shadow-primary/10">
                                <CardContent className="flex flex-col items-center justify-between gap-8 p-8 text-center sm:p-12 lg:flex-row lg:text-left">
                                    <div className="max-w-2xl">
                                        <h2 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                                            Prêt à mieux organiser votre réunion
                                            ?
                                        </h2>
                                        <p className="mt-3 text-primary-foreground/75">
                                            Créez votre espace, ajoutez vos
                                            membres et planifiez votre première
                                            assise.
                                        </p>
                                    </div>
                                    <Button
                                        asChild
                                        size="lg"
                                        variant="secondary"
                                        className="w-fit shrink-0"
                                    >
                                        <Link href={primaryRoute}>
                                            {auth.user
                                                ? 'Accéder au tableau de bord'
                                                : 'Commencer maintenant'}
                                            <ArrowRightIcon />
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </section>
                </main>

                <footer className="border-t">
                    <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-muted-foreground sm:flex-row sm:px-6 lg:px-8">
                        <div className="flex items-center gap-2 font-medium text-foreground">
                            <HandCoinsIcon className="size-4 text-primary" />{' '}
                            {name}
                        </div>
                        <p>
                            Une gestion collective plus claire, assise après
                            assise.
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
