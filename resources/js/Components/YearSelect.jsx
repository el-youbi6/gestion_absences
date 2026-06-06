import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';

export default function YearSelector() {
    const [annees, setAnnees] = useState([]);
    const [current, setCurrent] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Récupérer les années scolaires
        fetch(route('year.all'))
            .then((response) => response.json())
            .then((data) => {
                setAnnees(data.annees);
                setCurrent(data.current);
                setLoading(false);
            })
            .catch((error) => {
                console.error('Erreur lors du chargement des années:', error);
                setLoading(false);
            });
    }, []);

    const handleChange = (e) => {
        const anneeId = parseInt(e.target.value);
        router.post(route('year.set-active'), {
            annee_scolaire_id: anneeId,
        });
    };

    if (loading) {
        return (
            <div className="text-gray-500">
                Chargement...
            </div>
        );
    }

    return (
        <div className="text-right">
            <select
                value={current || ''}
                onChange={handleChange}
                className="rounded-md w-36 border-gray-300 py-1 px-2 text-sm shadow-sm focus:border-blue-400 focus:ring-blue-400"
            >
                {annees.map((annee) => (
                    <option key={annee.id} value={annee.id}>
                        {annee.libelle}
                    </option>
                ))}
            </select>
        </div>
    );
}
