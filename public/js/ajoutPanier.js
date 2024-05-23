$(document).ready(function() { // Ajouter produit au panier sans recharger la page et afficher un message flash
    $('.add-to-cart-form').on('submit', function(event) {
        event.preventDefault(); // Empêche le formulaire de se soumettre normalement

        let formData = $(this).serialize(); // Sérialise les données du formulaire

        let flashKey = 'success_' + window.location.pathname;

        let quantiteValue = $(this).find('select[name="quantite"]').val();

        let productName = $(this).find('[name="nomProduit"]').val(); // Récupération du nom du produit depuis le formulaire

        $.ajax({
            method: 'POST',
            url: "{{ path('app_panier_new') }}",
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Ajout du message flash de succès avec le nom du produit
                    let flashMessage = $('<div class="alert alert-success" role="alert" data-flash-key="' + flashKey + '">L\'article "' + productName + '" a bien été ajouté au panier</div>');
                    $('#flash-messages').append(flashMessage);

                    let notifQuantitePanier = document.getElementById('notifQuantitePanier');
                    let notifQuantitePanierInt = parseInt(notifQuantitePanier.textContent);
                    quantiteValue = parseInt(quantiteValue);
                    console.log(typeof notifQuantitePanierInt);
                    console.log(typeof quantiteValue);
                    notifQuantitePanier.textContent = (notifQuantitePanierInt + quantiteValue);

                    // Disparition du message après 5 secondes
                    setTimeout(function() {
                        $('[data-flash-key="' + flashKey + '"]').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 5000);
                } else {
                    // Ajout du message d'erreur
                    let errorMessage = $('<div class="alert alert-danger" role="alert">' + response.message + '</div>');
                    $('#flash-messages').append(errorMessage);
                    
                    // Disparition du message d'erreur après 5 secondes
                    setTimeout(function() {
                        errorMessage.fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la requête AJAX:', error);
            }
        })
    })
})