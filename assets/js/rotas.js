async function geocode(endereco) {

    const resposta = await fetch(
        "../api/geocode.php?endereco=" + encodeURIComponent(endereco)
    );

    const dados = await resposta.json();

    if (!dados.features.length) {
        return null;
    }

    return dados.features[0].geometry.coordinates;

}

async function buscarRota(origem, destino) {

    const resposta = await fetch(
        "../api/rota.php?origem=" +
        origem.join(",") +
        "&destino=" +
        destino.join(",")
    );

    return await resposta.json();

}
const iconeLoja = L.divIcon({
    className: "marcador-loja",
    html: `<div style="
        background:#f97316;
        width:36px;height:36px;
        border-radius:50% 50% 50% 0;
        transform:rotate(-45deg);
        display:flex;align-items:center;justify-content:center;
        box-shadow:0 2px 6px rgba(0,0,0,0.35);
        border:2px solid #fff;
    ">
        <span style="transform:rotate(45deg);font-size:18px;">🏪</span>
    </div>`,
    iconSize: [36, 36],
    iconAnchor: [18, 36],
    popupAnchor: [0, -36]
});

const iconeCliente = L.divIcon({
    className: "marcador-cliente",
    html: `<div style="
        background:#2563eb;
        width:36px;height:36px;
        border-radius:50% 50% 50% 0;
        transform:rotate(-45deg);
        display:flex;align-items:center;justify-content:center;
        box-shadow:0 2px 6px rgba(0,0,0,0.35);
        border:2px solid #fff;
    ">
        <span style="transform:rotate(45deg);font-size:18px;">📍</span>
    </div>`,
    iconSize: [36, 36],
    iconAnchor: [18, 36],
    popupAnchor: [0, -36]
});
document.querySelectorAll(".rota-mapa").forEach(async mapaDiv => {

    const iconeLoja = L.divIcon({
        className: "marcador-loja",
        html: `<div style="
            background:#f97316;
            width:36px;height:36px;
            border-radius:50% 50% 50% 0;
            transform:rotate(-45deg);
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 2px 6px rgba(0,0,0,0.35);
            border:2px solid #fff;
        ">
            <span style="transform:rotate(45deg);font-size:18px;">🏪</span>
        </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -36]
    });

    const iconeCliente = L.divIcon({
        className: "marcador-cliente",
        html: `<div style="
            background:#2563eb;
            width:36px;height:36px;
            border-radius:50% 50% 50% 0;
            transform:rotate(-45deg);
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 2px 6px rgba(0,0,0,0.35);
            border:2px solid #fff;
        ">
            <span style="transform:rotate(45deg);font-size:18px;">📍</span>
        </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -36]
    });

    const enderecoCliente = mapaDiv.dataset.endereco;

    const loja = await geocode(
        "Rua Rio de Janeiro, 471, Centro, Belo Horizonte, MG"
    );

    const cliente = await geocode(enderecoCliente);

    if (!loja || !cliente) {
        mapaDiv.innerHTML = "Não foi possível localizar o endereço.";
        return;
    }

    const mapa = L.map(mapaDiv);

    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        { attribution: "© OpenStreetMap" }
    ).addTo(mapa);

    const rota = await buscarRota(loja, cliente);
    const coordenadas = rota.features[0].geometry.coordinates;
    const pontos = coordenadas.map(c => [c[1], c[0]]);

    const linha = L.polyline(pontos, {
        color: "#2563eb",
        weight: 5
    }).addTo(mapa);

    // 👇 marcadores com os ícones personalizados
    L.marker([loja[1], loja[0]], { icon: iconeLoja })
        .addTo(mapa)
        .bindPopup("🏪 Lanchonete");

    L.marker([cliente[1], cliente[0]], { icon: iconeCliente })
        .addTo(mapa)
        .bindPopup("📍 Cliente");

    mapa.fitBounds(linha.getBounds(), { padding: [30, 30] });

    const distancia = (rota.features[0].properties.summary.distance / 1000).toFixed(2);
    const tempo = Math.round(rota.features[0].properties.summary.duration / 60);

    const info = document.getElementById(
        "info" + mapaDiv.id.replace("map", "")
    );

    info.innerHTML = `
        <div style="margin-top:10px">
            <strong>📏 Distância:</strong> ${distancia} km
            <br>
            <strong>🕒 Tempo estimado:</strong> ${tempo} minutos
        </div>
    `;

});