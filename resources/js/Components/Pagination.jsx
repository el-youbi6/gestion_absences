import { Link } from "@inertiajs/react";

export default function Pagination({ links, meta }) {
    return (
        <div className="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-slate-500">{meta}</p>
            <div className="flex flex-wrap gap-1">
                {links.map((link, index) => (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url || "#"}
                        preserveScroll
                        className={`rounded-md px-3 py-1.5 text-sm ${link.active
                                ? "bg-slate-900 text-white"
                                : link.url
                                    ? "bg-white text-slate-700 hover:bg-slate-100"
                                    : "cursor-not-allowed text-slate-300"
                            }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </div>
    );
}