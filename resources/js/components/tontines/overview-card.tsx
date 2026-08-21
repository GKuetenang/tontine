import { Card, CardContent } from "../ui/card";

type OverviewCardProps = {
    title: string;
    value: string | number;
    description: string;
    icon: React.ElementType;
};

export function OverviewCard({
    title,
    value,
    description,
    icon: Icon,
}: OverviewCardProps) {
    return (
        <Card>
            <CardContent className="flex items-start justify-between">
                <div className="space-y-1">
                    <p className="text-sm text-muted-foreground">
                        {title}
                    </p>

                    <p className="text-2xl font-semibold">
                        {value}
                    </p>

                    <p className="text-xs text-muted-foreground">
                        {description}
                    </p>
                </div>

                <div className="rounded-lg bg-primary/10 p-2 text-primary">
                    <Icon className="size-5" />
                </div>
            </CardContent>
        </Card>
    );
}