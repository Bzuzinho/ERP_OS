import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';

type OrganizationData = {
    id: number;
    name: string;
    code: string | null;
    nif: string | null;
    email: string | null;
    phone: string | null;
    phone_secondary: string | null;
    fax: string | null;
    website: string | null;
    address: string | null;
    postal_code: string | null;
    city: string | null;
    county: string | null;
    district: string | null;
    country: string | null;
    president_name: string | null;
    iban: string | null;
    facebook_url: string | null;
    instagram_url: string | null;
    description: string | null;
    logo_path: string | null;
};

type Props = {
    organization: OrganizationData;
    logoUrl: string | null;
};

const inputClass =
    'w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-sm font-medium text-slate-700';
const errorClass = 'mt-1 text-xs text-red-600';

function SectionHeader({ title, subtitle }: { title: string; subtitle?: string }) {
    return (
        <div className="mb-4 border-b border-slate-100 pb-3">
            <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-500">{title}</h2>
            {subtitle ? <p className="mt-0.5 text-xs text-slate-400">{subtitle}</p> : null}
        </div>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className={labelClass}>{label}</label>
            {children}
            {error ? <p className={errorClass}>{error}</p> : null}
        </div>
    );
}

export default function OrganizationEdit({ organization, logoUrl }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: organization.name ?? '',
        code: organization.code ?? '',
        nif: organization.nif ?? '',
        email: organization.email ?? '',
        phone: organization.phone ?? '',
        phone_secondary: organization.phone_secondary ?? '',
        fax: organization.fax ?? '',
        website: organization.website ?? '',
        address: organization.address ?? '',
        postal_code: organization.postal_code ?? '',
        city: organization.city ?? '',
        county: organization.county ?? '',
        district: organization.district ?? '',
        country: organization.country ?? 'Portugal',
        president_name: organization.president_name ?? '',
        iban: organization.iban ?? '',
        facebook_url: organization.facebook_url ?? '',
        instagram_url: organization.instagram_url ?? '',
        description: organization.description ?? '',
    });

    const [logoPreview, setLogoPreview] = useState<string | null>(logoUrl);
    const [logoFile, setLogoFile] = useState<File | null>(null);
    const [logoProcessing, setLogoProcessing] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        setLogoFile(file);
        setLogoPreview(URL.createObjectURL(file));
    };

    const saveLogo = () => {
        if (!logoFile) return;
        setLogoProcessing(true);
        const form = new FormData();
        form.append('logo', logoFile);
        router.post(route('admin.settings.organization.update-logo'), form, {
            onFinish: () => {
                setLogoProcessing(false);
                setLogoFile(null);
            },
        });
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.settings.organization.update'));
    };

    return (
        <AdminLayout title="Organização" subtitle="Dados e configurações da organização">
            <Head title="Organização" />

            <div className="space-y-5 lg:max-w-3xl">

                {/* Logo */}
                <AppCard>
                    <SectionHeader title="Logótipo" subtitle="Imagem representativa da organização" />
                    <div className="flex flex-wrap items-start gap-5">
                        <div className="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            {logoPreview ? (
                                <img src={logoPreview} alt="Logótipo" className="h-full w-full object-contain p-1" />
                            ) : (
                                <span className="text-xs text-slate-400">Sem logo</span>
                            )}
                        </div>
                        <div className="flex flex-col gap-2">
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                className="hidden"
                                onChange={handleLogoChange}
                            />
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Escolher ficheiro
                            </button>
                            {logoFile ? (
                                <button
                                    type="button"
                                    onClick={saveLogo}
                                    disabled={logoProcessing}
                                    className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {logoProcessing ? 'A guardar...' : 'Guardar logótipo'}
                                </button>
                            ) : null}
                            <p className="text-xs text-slate-400">PNG, JPG, SVG ou WebP · máx. 2 MB</p>
                        </div>
                    </div>
                </AppCard>

                <form onSubmit={submit} className="space-y-5">
                    {/* Identificação */}
                    <AppCard>
                        <SectionHeader title="Identificação" subtitle="Dados legais da organização" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Nome da organização *" error={errors.name}>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className={inputClass}
                                    required
                                />
                            </Field>
                            <Field label="Código interno" error={errors.code}>
                                <input
                                    type="text"
                                    value={data.code}
                                    onChange={(e) => setData('code', e.target.value)}
                                    className={inputClass}
                                    placeholder="Ex: JFD"
                                />
                            </Field>
                            <Field label="NIF" error={errors.nif}>
                                <input
                                    type="text"
                                    value={data.nif}
                                    onChange={(e) => setData('nif', e.target.value)}
                                    className={inputClass}
                                    placeholder="500000000"
                                />
                            </Field>
                            <Field label="Nome do Presidente" error={errors.president_name}>
                                <input
                                    type="text"
                                    value={data.president_name}
                                    onChange={(e) => setData('president_name', e.target.value)}
                                    className={inputClass}
                                />
                            </Field>
                            <div className="sm:col-span-2">
                                <Field label="Descrição / Missão" error={errors.description}>
                                    <textarea
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={3}
                                        className={inputClass}
                                        placeholder="Breve descrição da organização..."
                                    />
                                </Field>
                            </div>
                        </div>
                    </AppCard>

                    {/* Contactos */}
                    <AppCard>
                        <SectionHeader title="Contactos" subtitle="Meios de contacto da organização" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Email" error={errors.email}>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={inputClass}
                                    placeholder="geral@organização.pt"
                                />
                            </Field>
                            <Field label="Website" error={errors.website}>
                                <input
                                    type="url"
                                    value={data.website}
                                    onChange={(e) => setData('website', e.target.value)}
                                    className={inputClass}
                                    placeholder="https://www.organização.pt"
                                />
                            </Field>
                            <Field label="Telefone" error={errors.phone}>
                                <input
                                    type="text"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    className={inputClass}
                                    placeholder="210 000 000"
                                />
                            </Field>
                            <Field label="Telefone alternativo" error={errors.phone_secondary}>
                                <input
                                    type="text"
                                    value={data.phone_secondary}
                                    onChange={(e) => setData('phone_secondary', e.target.value)}
                                    className={inputClass}
                                    placeholder="210 000 001"
                                />
                            </Field>
                            <Field label="Fax" error={errors.fax}>
                                <input
                                    type="text"
                                    value={data.fax}
                                    onChange={(e) => setData('fax', e.target.value)}
                                    className={inputClass}
                                    placeholder="210 000 002"
                                />
                            </Field>
                        </div>
                    </AppCard>

                    {/* Morada */}
                    <AppCard>
                        <SectionHeader title="Morada" subtitle="Localização física da organização" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="sm:col-span-2">
                                <Field label="Rua / Morada" error={errors.address}>
                                    <input
                                        type="text"
                                        value={data.address}
                                        onChange={(e) => setData('address', e.target.value)}
                                        className={inputClass}
                                        placeholder="Rua Exemplo, nº 1"
                                    />
                                </Field>
                            </div>
                            <Field label="Código Postal" error={errors.postal_code}>
                                <input
                                    type="text"
                                    value={data.postal_code}
                                    onChange={(e) => setData('postal_code', e.target.value)}
                                    className={inputClass}
                                    placeholder="1000-001"
                                />
                            </Field>
                            <Field label="Localidade" error={errors.city}>
                                <input
                                    type="text"
                                    value={data.city}
                                    onChange={(e) => setData('city', e.target.value)}
                                    className={inputClass}
                                />
                            </Field>
                            <Field label="Concelho" error={errors.county}>
                                <input
                                    type="text"
                                    value={data.county}
                                    onChange={(e) => setData('county', e.target.value)}
                                    className={inputClass}
                                />
                            </Field>
                            <Field label="Distrito" error={errors.district}>
                                <input
                                    type="text"
                                    value={data.district}
                                    onChange={(e) => setData('district', e.target.value)}
                                    className={inputClass}
                                />
                            </Field>
                            <Field label="País" error={errors.country}>
                                <input
                                    type="text"
                                    value={data.country}
                                    onChange={(e) => setData('country', e.target.value)}
                                    className={inputClass}
                                />
                            </Field>
                        </div>
                    </AppCard>

                    {/* Dados Bancários */}
                    <AppCard>
                        <SectionHeader title="Dados Bancários" subtitle="Para emissão de documentos financeiros" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="sm:col-span-2">
                                <Field label="IBAN" error={errors.iban}>
                                    <input
                                        type="text"
                                        value={data.iban}
                                        onChange={(e) => setData('iban', e.target.value.toUpperCase())}
                                        className={inputClass}
                                        placeholder="PT50 0000 0000 0000 0000 0000 0"
                                        maxLength={34}
                                    />
                                </Field>
                            </div>
                        </div>
                    </AppCard>

                    {/* Redes Sociais */}
                    <AppCard>
                        <SectionHeader title="Redes Sociais" subtitle="Presença digital da organização" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Facebook" error={errors.facebook_url}>
                                <input
                                    type="url"
                                    value={data.facebook_url}
                                    onChange={(e) => setData('facebook_url', e.target.value)}
                                    className={inputClass}
                                    placeholder="https://facebook.com/organização"
                                />
                            </Field>
                            <Field label="Instagram" error={errors.instagram_url}>
                                <input
                                    type="url"
                                    value={data.instagram_url}
                                    onChange={(e) => setData('instagram_url', e.target.value)}
                                    className={inputClass}
                                    placeholder="https://instagram.com/organização"
                                />
                            </Field>
                        </div>
                    </AppCard>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-3 pb-6">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-2xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {processing ? 'A guardar...' : 'Guardar alterações'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
