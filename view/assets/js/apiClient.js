export async function apiFetch(url, options = {}) {
    const response = await fetch(url, options);
    const text = await response.text();

    let payload;
    try {
        payload = JSON.parse(text);
    } catch (error) {
        console.error('Respuesta no JSON:', text);
        throw new Error('Respuesta JSON inválida del servidor.');
    }

    if (!payload || typeof payload !== 'object') {
        throw new Error('Contrato de respuesta inválido.');
    }

    if (payload.code !== response.status) {
        throw new Error(payload.message || 'El código HTTP no coincide con la respuesta.');
    }

    if (!response.ok || payload.status !== 'success') {
        throw new Error(payload.message || `Error HTTP ${response.status}`);
    }

    return payload;
}
