import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';

type Props = {
    passwordRules: string;
};

export default function RegisterCoordinator({ passwordRules }: Props) {
    return (
        <>
            <Head title="Coordinator registratie" />

            <Form
                action="/register/coordinator"
                method="post"
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Contactpersoon</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Volledige naam"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mailadres</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Telefoonnummer</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    required
                                    tabIndex={3}
                                    autoComplete="tel"
                                    name="phone"
                                    placeholder="Telefoonnummer"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="organisation_name">
                                    Organisatienaam
                                </Label>
                                <Input
                                    id="organisation_name"
                                    type="text"
                                    required
                                    tabIndex={4}
                                    name="organisation_name"
                                    placeholder="Organisatie"
                                />
                                <InputError
                                    message={errors.organisation_name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="city">Plaats</Label>
                                <Input
                                    id="city"
                                    type="text"
                                    tabIndex={5}
                                    name="city"
                                    placeholder="Plaats"
                                />
                                <InputError message={errors.city} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="vat_number">
                                    Btw-nummer (optioneel)
                                </Label>
                                <Input
                                    id="vat_number"
                                    type="text"
                                    tabIndex={6}
                                    name="vat_number"
                                    placeholder="BE0123456789"
                                />
                                <InputError message={errors.vat_number} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="website">
                                    Website (optioneel)
                                </Label>
                                <Input
                                    id="website"
                                    type="url"
                                    tabIndex={7}
                                    name="website"
                                    placeholder="https://example.com"
                                />
                                <InputError message={errors.website} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Wachtwoord</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={8}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Wachtwoord"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Bevestig wachtwoord
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={9}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Bevestig wachtwoord"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={10}
                                data-test="register-coordinator-button"
                            >
                                {processing && <Spinner />}
                                Aanvraag indienen
                            </Button>
                        </div>

                        <div className="space-y-2 text-center text-sm text-muted-foreground">
                            <div>
                                Heb je al een account?{' '}
                                <TextLink href={login()} tabIndex={11}>
                                    Inloggen
                                </TextLink>
                            </div>

                            <div>
                                Ben je crewlid?{' '}
                                <TextLink href="/register" tabIndex={12}>
                                    Regulier account aanmaken
                                </TextLink>
                            </div>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

RegisterCoordinator.layout = {
    title: 'Coordinatorregistratie',
    description: 'Maak je coordinatorprofiel aan en dien je aanvraag in',
};
