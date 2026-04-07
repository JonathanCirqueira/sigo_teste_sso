import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: Props) {
    return (
        <>
            <Head title="Log in" />

            <div className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <Button
                        asChild
                        className="mt-4 w-full h-11 text-base font-semibold bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm transition-all active:scale-[0.98]"
                        data-test="sso-login-button"
                    >
                        <a href="/auth/redirect">
                            Entrar com Sigo SSO
                        </a>
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Acesse sua conta utilizando o provedor centralizado da empresa.
                </div>
            </div>

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Log in to your account',
    description: 'Enter your email and password below to log in',
};
