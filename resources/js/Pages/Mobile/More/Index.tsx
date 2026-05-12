import MobileLayout from '@/Layouts/MobileLayout';
import { Link } from '@inertiajs/react';
import { type PageProps } from '@/types';

type MoreProps = {
    user: {
        id: number;
        name: string;
        email: string;
        avatar_url?: string;
    };
};

export default function MoreIndex(props: PageProps<MoreProps>) {
    const { user } = props;

    return (
        <MobileLayout title="Mais">
            <div className="p-4 space-y-4">
                {/* User Profile Card */}
                <div className="bg-white p-4 rounded-lg border border-gray-200">
                    <div className="flex items-center gap-3">
                        {user.avatar_url ? (
                            <img src={user.avatar_url} alt={user.name} className="h-12 w-12 rounded-full" />
                        ) : (
                            <div className="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                {user.name
                                    .split(' ')
                                    .slice(0, 2)
                                    .map(n => n[0])
                                    .join('')}
                            </div>
                        )}
                        <div className="flex-1">
                            <h2 className="font-medium text-gray-900">{user.name}</h2>
                            <p className="text-sm text-gray-500">{user.email}</p>
                        </div>
                    </div>
                </div>

                {/* Menu Options */}
                <div className="space-y-2">
                    <h3 className="text-sm font-medium text-gray-700">Opções</h3>

                    <Link
                        href={route('profile.edit')}
                        className="block px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                    >
                        <p className="font-medium text-gray-900">Editar Perfil</p>
                        <p className="text-sm text-gray-500">Altere seus dados pessoais</p>
                    </Link>

                    <Link
                        href={route('mobile.today.index')}
                        className="block px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                    >
                        <p className="font-medium text-gray-900">Voltar ao Início</p>
                        <p className="text-sm text-gray-500">Veja suas tarefas de hoje</p>
                    </Link>
                </div>

                {/* About Section */}
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h3 className="font-medium text-blue-900 mb-2">Modo Terreno Mobile</h3>
                    <p className="text-sm text-blue-800">
                        Versão otimizada para equipas em terreno. Execute tarefas, adicione observações e anexos diretamente do seu telemóvel.
                    </p>
                </div>

                {/* Logout */}
                <form method="POST" action={route('logout')}>
                    <input type="hidden" name="_token" value={(window as any).csrf_token} />
                    <button
                        type="submit"
                        className="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors"
                    >
                        Terminar Sessão
                    </button>
                </form>
            </div>
        </MobileLayout>
    );
}
