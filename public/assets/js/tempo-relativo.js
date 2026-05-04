function calcularTempoRelativo(dataPublicacao) {
    const agora     = new Date();
    const publicado = new Date(dataPublicacao.replace(' ', 'T') + 'Z');
    const diffMs    = agora - publicado;
    const diffMin   = Math.floor(diffMs / 60000);
    const diffHoras = Math.floor(diffMin / 60);
    const diffDias  = Math.floor(diffHoras / 24);
    const diffMeses = Math.floor(diffDias / 30);
    const diffAnos  = Math.floor(diffDias / 365);

    if (diffAnos >= 1)  return `Há ${diffAnos}a`;
    if (diffMeses >= 1) return `Há ${diffMeses} ${diffMeses > 1 ? 'meses' : 'mês'}`;
    if (diffDias >= 1)  return `Há ${diffDias}d`;
    if (diffHoras >= 1) return `Há ${diffHoras}h`;
    if (diffMin >= 1)   return `Há ${diffMin}min`;
    return 'Agora mesmo';
}

function atualizarTodos() {
    document.querySelectorAll('.tempo-relativo').forEach(el => {
        const dataPublicacao = el.getAttribute('data-publicacao');
        if (dataPublicacao) {
            el.textContent = calcularTempoRelativo(dataPublicacao);
        }
    });
}

atualizarTodos();
setInterval(atualizarTodos, 60000);