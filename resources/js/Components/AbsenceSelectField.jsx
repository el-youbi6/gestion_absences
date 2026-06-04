export default function AbsenceSelectField({
    label,
    value,
    onChange,
    options,
    placeholder,
    error,
    disabled = false,
}) {
    return (
        <label className="block">
            <span className="mb-2 block text-sm font-semibold text-slate-700">
                {label}
            </span>
            <select
                value={value}
                disabled={disabled}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
            >
                <option value="">{placeholder}</option>
                {options.map((option) => (
                    <option key={option.id} value={option.id}>
                        {option.nom}
                    </option>
                ))}
            </select>
            {error && (
                <span className="mt-2 block text-sm text-red-600">{error}</span>
            )}
        </label>
    );
}
