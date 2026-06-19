import { Form } from '@inertiajs/react';
import DashboardController from '@/actions/App/Http/Controllers/DashboardController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export function PublicEventsSearch({
    search,
    hasActiveSearch,
    onSearchChange,
    onReset,
}: {
    search: string;
    hasActiveSearch: boolean;
    onSearchChange: (value: string) => void;
    onReset: () => void;
}) {
    return (
        <Form
            {...DashboardController.index.form()}
            options={{
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }}
            className="flex flex-col gap-2 sm:flex-row"
        >
            <Input
                name="search"
                value={search}
                onChange={(event) => onSearchChange(event.currentTarget.value)}
                placeholder="Zoek op event, locatie of organisatie"
                className="sm:max-w-lg"
            />
            <div className="flex gap-2">
                <Button type="submit">Zoek</Button>
                {hasActiveSearch && (
                    <Button type="button" variant="outline" onClick={onReset}>
                        Reset
                    </Button>
                )}
            </div>
        </Form>
    );
}
