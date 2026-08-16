import { Link, useLocation } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateLabel } from '@/lib/datetime';

interface ConfirmationState {
  bookingGroupId?: string;
  status?: string;
  date?: string;
  slots?: string[];
  eventTypeName?: string;
}

/** FR-4: on-page booking confirmation (no email in v1). */
export function ConfirmationPage() {
  const { state } = useLocation();
  const data = (state ?? {}) as ConfirmationState;

  if (!data.bookingGroupId) {
    return (
      <main className="mx-auto max-w-md p-4">
        <Card>
          <CardHeader>
            <CardTitle>Нет данных о брони</CardTitle>
          </CardHeader>
          <CardContent>
            <Link to="/">
              <Button>Вернуться к бронированию</Button>
            </Link>
          </CardContent>
        </Card>
      </main>
    );
  }

  return (
    <main className="mx-auto max-w-md p-4">
      <Card>
        <CardHeader>
          <CardTitle>Бронь принята</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-col gap-2 text-sm">
          <p>
            Статус:{' '}
            <strong>{data.status === 'pending' ? 'ожидает подтверждения' : data.status}</strong>
          </p>
          {data.eventTypeName && <p>Событие: {data.eventTypeName}</p>}
          {data.date && <p className="capitalize">Дата: {formatDateLabel(data.date)}</p>}
          {data.slots && data.slots.length > 0 && <p>Слоты: {data.slots.join(', ')}</p>}
          <p className="text-muted-foreground">Номер брони: {data.bookingGroupId}</p>
          <Link to="/" className="mt-2 text-primary underline">
            Забронировать ещё
          </Link>
        </CardContent>
      </Card>
    </main>
  );
}
