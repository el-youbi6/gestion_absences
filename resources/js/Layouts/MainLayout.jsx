import Sidebar from '../Components/Sidebar';

export default function MainLayout({ children }) {
    return (
        <div className="h-full bg-slate-50 text-slate-900">
            <div className="flex min-h-screen">
                <Sidebar />

                <main className="flex-1 p-6">
                    <div className="mx-auto max-w-7xl">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
