import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export function PublicEventsSearch({
    search,
    hasActiveSearch,
    onSearchChange,
    onSubmit,
    onReset,
}: {
    search: string;
    hasActiveSearch: boolean;
    onSearchChange: (value: string) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    onReset: () => void;
}) {
    return (
        <form onSubmit={onSubmit} className="flex flex-col gap-2 sm:flex-row">
            <Input
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
        </form>
    );
}
