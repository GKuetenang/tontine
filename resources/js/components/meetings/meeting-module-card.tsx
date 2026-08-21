import { Button } from "../ui/button";

export function MeetingModuleCard({
    title,
    description,
    href,
    icon: Icon,
}: {
    title: string;
    description: string;
    href: string;
    icon: React.ElementType;
}) {
    return (
        <Button
            asChild
            variant="outline"
            className="h-auto justify-start p-4"
        >
            <a
                href={href}
                className="flex items-start gap-3"
            >
                <div className="rounded-md bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </div>

                <div className="min-w-0 text-left">
                    <p className="font-medium">
                        {title}
                    </p>

                    <p className="mt-1 whitespace-normal text-sm font-normal text-muted-foreground">
                        {description}
                    </p>
                </div>
            </a>
        </Button>
    );
}