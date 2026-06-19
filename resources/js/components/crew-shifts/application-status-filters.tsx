import type {
    ApplicationFilter,
    ApplicationStatus,
} from '@/components/crew-shifts/types';
import { Button } from '@/components/ui/button';

export function ApplicationStatusFilters({
    filter,
    total,
    countByStatus,
    onFilterChange,
}: {
    filter: ApplicationFilter;
    total: number;
    countByStatus: Record<ApplicationStatus, number>;
    onFilterChange: (nextFilter: ApplicationFilter) => void;
}) {
    return (
        <div className="flex flex-wrap gap-2">
            <Button
                type="button"
                variant={filter === 'all' ? 'default' : 'outline'}
                onClick={() => onFilterChange('all')}
            >
                Alles ({total})
            </Button>
            <Button
                type="button"
                variant={filter === 'pending' ? 'default' : 'outline'}
                onClick={() => onFilterChange('pending')}
            >
                Pending ({countByStatus.pending})
            </Button>
            <Button
                type="button"
                variant={filter === 'approved' ? 'default' : 'outline'}
                onClick={() => onFilterChange('approved')}
            >
                Goedgekeurd ({countByStatus.approved})
            </Button>
            <Button
                type="button"
                variant={filter === 'rejected' ? 'default' : 'outline'}
                onClick={() => onFilterChange('rejected')}
            >
                Afgewezen ({countByStatus.rejected})
            </Button>
        </div>
    );
}
