document.addEventListener("DOMContentLoaded", (event) => {
    const searchInput = document.getElementById("search-input");
    const suggestionsContainer = document.getElementById("suggestions");
  
    // Fonction pour aller chercher les suggestions d'autocomplétion
    function fetchSuggestions() {
      const query = searchInput.value;
  
      // Si la requête est trop courte (< 2 caractères), on cache les suggestions
      if (query.length < 2) {
        suggestionsContainer.style.display = "none";
        return;
      }
  
      // Requête au serveur pour obtenir des suggestions (endpoint `/autocomplete`)
      fetch(`/autocomplete?query=${encodeURIComponent(query)}`)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erreur réseau lors de la récupération des suggestions");
          }
          return response.json();
        })
        .then((suggestions) => {
          // Vider et afficher le conteneur des suggestions
          suggestionsContainer.innerHTML = "";
          suggestionsContainer.style.display = "block";
  
          if (suggestions.length === 0) {
            suggestionsContainer.style.display = "none";
            return;
          }
  
          // Pour chaque suggestion, on crée un élément div et on l'ajoute au conteneur
          suggestions.forEach((suggestion) => {
            const div = document.createElement("div");
            div.textContent = suggestion.name; // Le nom de l'artiste est affiché
            div.classList.add("suggestion-item");
  
            // Si on clique sur la suggestion, redirection vers la page de l'artiste
            div.onclick = () => {
              window.location.href = `/artists/${suggestion.id}`;
            };
            suggestionsContainer.appendChild(div);
          });
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des suggestions :", error);
        });
    }
  
    // Déclenche la fonction de suggestions à chaque fois qu'on tape dans la barre de recherche
    searchInput.addEventListener("keyup", fetchSuggestions);
  
    // Quand on clique en dehors des suggestions, elles disparaissent
    document.addEventListener("click", function (event) {
      if (!suggestionsContainer.contains(event.target) && event.target !== searchInput) {
        suggestionsContainer.style.display = "none";
      }
    });
  });
  