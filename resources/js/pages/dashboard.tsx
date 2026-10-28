import BirthdayAlert from '@/components/dashboard/birthday-alert';
import MyRequests from '@/components/dashboard/my-requests';
import PendingApprovals from '@/components/dashboard/pending-approvals';
import ShortcutList from '@/components/dashboard/shortcut-list';
import UpcomingList from '@/components/dashboard/upcoming-list';
import YearProgress, { type CommitteeProgress, type Kpis } from '@/components/dashboard/year-progress';
import MainLayout from '@/layouts/main-layout';
import { decidesApprovals } from '@/lib/roles';
import type { ApprovalItem } from '@/types/approvals';
import type { SoonBirthdays } from '@/types/birthdays';
import type { OwnActivity } from '@/types/schedule';
import type { SharedProps } from '@/types/shared';
import { usePage } from '@inertiajs/react';

interface Props {
    pendingApprovals: ApprovalItem[];
    kpis: Kpis;
    myRequests?: ApprovalItem[];
    upcoming?: OwnActivity[];
    committeeProgress?: CommitteeProgress[];
    birthdays?: SoonBirthdays;
}

function greeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) return 'Buenos días';
    if (hour < 19) return 'Buenas tardes';

    return 'Buenas noches';
}

/**
 * La pantalla de inicio (HU-17). Antes que nada, lo que espera la decisión del cargo y desde
 * cuándo; luego en qué va lo que la persona envió, cómo va el año y lo que viene.
 */
export default function Dashboard({ pendingApprovals, kpis, myRequests, upcoming, committeeProgress, birthdays }: Props) {
    const { auth, scope, pendingCount, pendingSince } = usePage<SharedProps>().props;
    const decides = decidesApprovals(auth.user?.isPastor, scope.activeRole?.role);

    return (
        <MainLayout title={greeting()} subtitle={auth.user?.name}>
            <BirthdayAlert birthdays={birthdays} />
            {decides && <PendingApprovals items={pendingApprovals} count={pendingCount} since={pendingSince} />}
            {myRequests && myRequests.length > 0 && <MyRequests items={myRequests} />}

            <div className="lg:grid lg:grid-cols-2 lg:items-start lg:gap-6">
                <YearProgress kpis={kpis} committees={committeeProgress} />
                <UpcomingList items={upcoming} />
            </div>

            <ShortcutList />
        </MainLayout>
    );
}
