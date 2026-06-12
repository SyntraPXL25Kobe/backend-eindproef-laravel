import { Head, usePage } from '@inertiajs/react';
import ProfileSkillsDialog from '@/components/profile-skills-dialog';
import { edit } from '@/routes/skills';

type SkillOption = {
    id: number;
    name: string;
    description: string | null;
};

type PageProps = {
    availableSkills: SkillOption[];
    userSkills: number[];
};

export default function Skills() {
    const { availableSkills, userSkills } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Skills settings" />

            <h1 className="sr-only">Skills settings</h1>

            <ProfileSkillsDialog
                availableSkills={availableSkills}
                userSkills={userSkills}
            />
        </>
    );
}

Skills.layout = {
    breadcrumbs: [
        {
            title: 'Skills settings',
            href: edit(),
        },
    ],
};
