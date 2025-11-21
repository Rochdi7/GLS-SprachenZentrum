<form action="#" method="POST" class="gls-form">
    @csrf
    
    <div class="row g-4">

        <!-- NAME -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Votre nom complet" required>
        </div>

        <!-- PHONE -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Phone *</label>
            <div class="input-group">
                <span class="input-group-text">+212</span>
                <input 
                    type="text" 
                    name="phone" 
                    class="form-control" 
                    placeholder="0650-123456"
                    required>
            </div>
        </div>

        <!-- EMAIL -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Email *</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
        </div>

        <!-- ADDRESS -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Adresse *</label>
            <input type="text" name="adresse" class="form-control" placeholder="Votre adresse complète" required>
        </div>

        <!-- LEVEL -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Niveau de langue allemande *</label>
            <select name="niveau" class="form-select" required>
                <option value="" disabled selected>Choisissez votre niveau</option>
                <option value="A1">A1 - Débutant</option>
                <option value="A2">A2 - Élémentaire</option>
                <option value="B1">B1 - Intermédiaire</option>
                <option value="B2">B2 - Avancé</option>
                <option value="C1">C1 - Autonome</option>
            </select>
        </div>

        <!-- TYPE DE COURS -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Type de cours</label>
            <select name="type_cours" class="form-select">
                <option value="" disabled selected>Choisissez le type</option>
                <option value="presentiel">Cours présentiel</option>
                <option value="en_ligne">Cours en ligne</option>
            </select>
        </div>

        <!-- HORAIRE PRÉFÉRÉ -->
        <div class="col-md-6">
            <label class="form-label fw-bold">Horaire préféré</label>
            <input type="time" name="horaire_prefere" class="form-control">
        </div>

        <!-- START DATE -->
        <div class="col-md-6">
            <label class="form-label fw-bold">À partir de...</label>
            <input type="date" name="date_start" class="form-control">
        </div>

        <!-- GLS CENTER -->
        <div class="col-12">
            <label class="form-label fw-bold">Centre GLS préféré *</label>
            <select name="centre" class="form-select" required>
                <option value="" disabled selected>Choisissez un centre</option>
                <option>GLS Agdal, Rabat</option>
                <option>GLS Salé</option>
                <option>GLS Kénitra</option>
                <option>GLS Casablanca</option>
                <option>GLS Marrakech</option>
                <option>GLS Agadir</option>
            </select>
        </div>

        <!-- SUBMIT -->
        <div class="col-12 mt-4 text-center">
            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold">
                Envoyer la demande
            </button>
        </div>

    </div>
</form>

<style>
    .gls-form .form-label {
    font-size: 1rem;
    margin-bottom: .35rem;
}

.gls-form .form-control,
.gls-form .form-select {
    border-radius: 12px;
    padding: .75rem 1rem;
    font-size: 1rem;
}

.gls-form .btn-primary {
    background: var(--blue);
    border: none;
    border-radius: 14px;
    font-size: 1.15rem;
}

</style>