# ⚡ QUICK START - Commandes à Exécuter

## 1️⃣ Préparer l'Application (2 min)

```bash
# Vérifier qu'il n'y a pas d'erreurs
php artisan config:cache

# Compiler le frontend (si modifié)
npm run build

# Optionnel - Repartir de zéro
php artisan migrate:fresh
php artisan db:seed
```

## 2️⃣ Vérifier les Changements (3 min)

```bash
# Les routes existent?
php artisan route:list | grep academic

# Devrait afficher:
# POST   /academic-year/set-active
# GET    /academic-year/all
```

## 3️⃣ Tester avec Tinker (2 min)

```bash
php artisan tinker

# Test 1: Année actuelle calculée
> App\Services\AcademicYearService::getCurrentAcademicYear()
# Réponse: "2025/2026" (ou l'année correcte)

# Test 2: Année créée/récupérée
> App\Services\AcademicYearService::getOrCreateCurrentAcademicYear()
# Réponse: AnneeScolaire object

# Test 3: Session initialisée
> App\Services\AcademicYearService::initializeSession()
> session('annee_scolaire_id')
# Réponse: 1 (ou l'ID de l'année)

# Exit
> exit
```

## 4️⃣ Lancer l'Application (∞)

```bash
# Démarrer le serveur
php artisan serve

# Accéder à http://localhost:8000/dashboard
```

## 5️⃣ Vérifier Visuellement (1 min)

### ✅ Checklist:
- [ ] Page s'ouvre sans erreur
- [ ] Navbar visible en haut
- [ ] "Année scolaire: [2025/2026 ▼]" visible dans la navbar
- [ ] Dropdown cliquable
- [ ] Autres années listées dans le dropdown
- [ ] Page se met à jour quand on change l'année

---

## 🧪 Tester les Imports (5 min)

### 1. Créer un fichier Excel de test

Structure Excel avec 7 feuilles:

#### Feuille 1: "filieres"
```
| nom           |
|---------------|
| Informatique  |
| Électricité   |
```

#### Feuille 2: "groupes"
```
| nom   | nom_filiere  |
|-------|--------------|
| 1ère  | Informatique |
| 2nde  | Électricité  |
```

#### Feuille 3: "modules"
```
| nom        | nom_filiere  |
|------------|--------------|
| PHP        | Informatique |
| Hardware   | Électricité  |
```

#### Feuille 4: "formateurs"
```
| nom   | prenom | cin    | email         | password  |
|-------|--------|--------|---------------|-----------|
| Dupont| Jean   | AB1234 | jean@test.com | password1 |
```

#### Feuille 5: "stagiaires"
```
| nom     | prenom | cin    | email          | password  | nom_group |
|---------|--------|--------|----------------|-----------|-----------|
| Martin  | Paul   | CD5678 | paul@test.com  | password2 | 1ère      |
| Bernard | Marie  | EF9012 | marie@test.com | password3 | 2nde      |
```

#### Feuille 6: "formateur_groupe"
```
| formateur_cin | groupe |
|---------------|--------|
| AB1234        | 1ère   |
| AB1234        | 2nde   |
```

#### Feuille 7: "formateur_module"
```
| formateur_cin | module |
|---------------|--------|
| AB1234        | PHP    |
| AB1234        | Hardware |
```

### 2. Charger le fichier

```
1. Aller sur http://localhost:8000/Import
2. Charger le fichier Excel
3. Attendre que l'import soit terminé
4. Vérifier le message de succès
```

### 3. Vérifier les données

```php
# En tinker:
php artisan tinker

# Test 1: Groupes créés
> App\Models\Groupe::count()
# Réponse: 2 (1ère et 2nde)

# Test 2: Groupes liés à l'année
> App\Models\Groupe::where('annee_scolaire_id', 1)->count()
# Réponse: 2 (tous les groupes)

# Test 3: Stagiaires créés
> App\Models\Stagiaire::count()
# Réponse: 2 (Martin et Marie)

# Test 4: Vérifier les rôles
> App\Models\User::where('role', 'stagiaire')->count()
# Réponse: 2 (Martin et Marie)

> App\Models\User::where('role', 'formateur')->count()
# Réponse: 1 (Dupont)
```

---

## 📊 Vérifier la Multi-Année

### Créer une 2ème année (simulate new year):

```php
php artisan tinker

# Créer une nouvelle année
> App\Models\AnneeScolaire::create(['libelle' => '2026/2027'])
# Réponse: AnneeScolaire object

# Changer la session à cette année
> App\Services\AcademicYearService::setActiveAcademicYear(2)
> session('annee_scolaire_id')
# Réponse: 2

# Créer un groupe pour l'année 2
> App\Models\Groupe::create([
    'nom' => '3ème',
    'filiere_id' => 1,
    'annee_scolaire_id' => 2
  ])

# Compter les groupes de l'année 2
> App\Models\Groupe::where('annee_scolaire_id', 2)->count()
# Réponse: 1 (juste 3ème)

# Revenir à l'année 1
> App\Services\AcademicYearService::setActiveAcademicYear(1)
> App\Models\Groupe::where('annee_scolaire_id', 1)->count()
# Réponse: 2 (1ère et 2nde)

# Vérifier toutes les années
> App\Models\AnneeScolaire::all()
# Réponse: Collection with 2 items
```

---

## 🚨 Troubleshooting

### ❌ "Route not found"
```bash
php artisan route:cache
php artisan route:clear
php artisan config:cache
```

### ❌ "Service not found"
```bash
php artisan config:cache
```

### ❌ "Middleware not applied"
```bash
# Vérifier bootstrap/app.php
cat bootstrap/app.php | grep InitializeAcademicYear
# Devrait afficher la ligne du middleware
```

### ❌ "Composant React ne charge pas"
```bash
# Compiler le frontend
npm run build

# Si en développement:
npm run dev
```

### ❌ "Erreur SQL: foreign key"
```bash
# L'année scolaire n'a pas pu être déterminée
# Vérifier:
php artisan tinker
> App\Models\AnneeScolaire::count()
# Si 0: créer une année manuellement
> App\Models\AnneeScolaire::create(['libelle' => '2025/2026'])
```

---

## 📈 Progression Attendue

```
✅ php artisan config:cache
   ↓
✅ Middleware enregistré
   ↓
✅ Routes accessibles
   ↓
✅ Tinker tests réussis
   ↓
✅ Application démarre
   ↓
✅ Navbar affiche sélecteur
   ↓
✅ Importer fonctionne
   ↓
✅ Données liées à l'année
   ↓
✅ Multi-année fonctionne
   ↓
🎉 SUCCESS!
```

---

## 🎯 Temps Total

| Étape | Temps | Cumul |
|-------|-------|-------|
| Préparer | 2 min | 2 min |
| Vérifier | 3 min | 5 min |
| Tinker | 2 min | 7 min |
| Application | 1 min | 8 min |
| Vérifier | 1 min | 9 min |
| Importer | 5 min | 14 min |
| Multi-année | 5 min | 19 min |
| **TOTAL** | | **~20 min** |

---

## 💻 Copier-Coller Rapide

### Commande complète (exécuter dans ordre):

```bash
# 1. Préparer
php artisan config:cache

# 2. Vérifier les routes
php artisan route:list | grep academic

# 3. Démarrer
php artisan serve

# 4. Dans un autre terminal - Tinker rapide
php artisan tinker << 'EOF'
echo "Test 1:"; 
echo App\Services\AcademicYearService::getCurrentAcademicYear();
echo "\nTest 2:";
App\Services\AcademicYearService::initializeSession();
echo session('annee_scolaire_id');
echo "\n✅ OK\n";
exit;
EOF
```

---

✅ **C'est tout! Tu es maintenant opérationnel!**

Si tu rencontres un problème:
1. Vérifier `storage/logs/laravel.log`
2. Vérifier la console du navigateur (F12)
3. Consulter la section Troubleshooting ci-dessus
