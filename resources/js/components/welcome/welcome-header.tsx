import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import { Link, usePage } from '@inertiajs/react';
import AppLogo from '../app-logo';

type Props = { authenticated: boolean };

export function WelcomeHeader({ authenticated }: Props) {
    const { name } = usePage().props;

    return (
        <header className="sticky top-0 z-50 border-b bg-background/90 backdrop-blur-xl">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" className="flex items-center gap-2.5">
                    {/* <span className="grid size-9 place-items-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                        <HandCoinsIcon className="size-5" />
                    </span>
                    <span className="text-lg font-semibold tracking-tight">
                        {name}
                    </span> */}
                    <AppLogo />
                </Link>

                <nav className="flex items-center gap-2">
                    {authenticated ? (
                        <Button asChild>
                            <Link href={dashboard()}>Tableau de bord</Link>
                        </Button>
                    ) : (
                        <>
                            <Button asChild >
                                <Link href={login()}>Se connecter</Link>
                            </Button>
                            {/* <Button asChild>
                                <Link href={register()}>Créer un compte</Link>
                            </Button> */}
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
