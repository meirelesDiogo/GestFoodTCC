async function geocode(endereco){

    const resposta = await fetch(
        "../api/geocode.php?endereco=" + encodeURIComponent(endereco)
    );

    const dados = await resposta.json();

    if(!dados.features.length){
        return null;
    }

    return dados.features[0].geometry.coordinates;

}

document.querySelectorAll(".rota-mapa").forEach(async mapaDiv =>{

    const enderecoCliente = mapaDiv.dataset.endereco;

    const loja = await geocode(
        "Rua Rio de Janeiro 471 Centro Belo Horizonte MG"
    );

    const cliente = await geocode(enderecoCliente);

    if(!loja || !cliente){

        mapaDiv.innerHTML="Não foi possível localizar o endereço.";

        return;

    }

    const mapa = L.map(mapaDiv).setView(
        [loja[1],loja[0]],
        13
    );

    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            attribution:"© OpenStreetMap"
        }
    ).addTo(mapa);

    L.marker([loja[1],loja[0]])
        .addTo(mapa)
        .bindPopup("Lanchonete");

    L.marker([cliente[1],cliente[0]])
        .addTo(mapa)
        .bindPopup("Cliente");

});