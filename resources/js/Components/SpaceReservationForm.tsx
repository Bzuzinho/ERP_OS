import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type SpaceOption = { id: number; name: string };
type ContactOption = { id: number; name: string };

type Props = {
    spaces: SpaceOption[];
    contacts: ContactOption[];
    submitRoute: string;
};

export default function SpaceReservationForm({ spaces, contacts, submitRoute }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        space_id: spaces[0]?.id ?? 0,
        contact_id: contacts[0]?.id ?? 0,
        start_at: '',
        end_at: '',
        purpose: '',
        notes: '',
    });

    const onSubmit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(submitRoute);
    };

    return (
        <form onSubmit={onSubmit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
            <div>
                <InputLabel htmlFor="space_id" value="Espaco" />
                <select id="space_id" value={data.space_id} onChange={(event) => setData('space_id', Number(event.target.value))} className="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    {spaces.map((space) => (
                        <option key={space.id} value={space.id}>
                            {space.name}
                        </option>
                    ))}
                </select>
            </div>
            <div>
                <InputLabel htmlFor="contact_id" value="Contacto" />
                <select id="contact_id" value={data.contact_id} onChange={(event) => setData('contact_id', Number(event.target.value))} className="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value={0}>Sem contacto</option>
                    {contacts.map((contact) => (
                        <option key={contact.id} value={contact.id}>
                            {contact.name}
                        </option>
                    ))}
                </select>
            </div>
            <div>
                <InputLabel htmlFor="start_at" value="Data/hora inicio" />
                <TextInput id="start_at" type="datetime-local" value={data.start_at} onChange={(event) => setData('start_at', event.target.value)} className="mt-1 block w-full" />
                {errors.start_at ? <p className="mt-1 text-xs text-rose-700">{errors.start_at}</p> : null}
            </div>
            <div>
                <InputLabel htmlFor="end_at" value="Data/hora fim" />
                <TextInput id="end_at" type="datetime-local" value={data.end_at} onChange={(event) => setData('end_at', event.target.value)} className="mt-1 block w-full" />
                {errors.end_at ? <p className="mt-1 text-xs text-rose-700">{errors.end_at}</p> : null}
            </div>
            <div>
                <InputLabel htmlFor="purpose" value="Finalidade" />
                <TextInput id="purpose" value={data.purpose} onChange={(event) => setData('purpose', event.target.value)} className="mt-1 block w-full" />
            </div>
            <div>
                <InputLabel htmlFor="notes" value="Observacoes" />
                <textarea id="notes" value={data.notes} onChange={(event) => setData('notes', event.target.value)} className="mt-1 w-full rounded-lg border-slate-300 text-sm" rows={4} />
            </div>
            <PrimaryButton disabled={processing}>Submeter pedido</PrimaryButton>
        </form>
    );
}
