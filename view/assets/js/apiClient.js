// Llama a la API con fetch y valida que la respuesta sea JSON
export async function apiFetch(url, options = {}) {
    const { allowedStatuses = [], ...fetchOptions } = options;

    const response = await fetch(url, fetchOptions);
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
        throw new Error(payload.message || 'El código HTTP no coincide con la respuesta del servidor.');
    }

    if (allowedStatuses.includes(response.status)) {
        return payload;
    }

    const isServerSuccess = payload.status && payload.status.toLowerCase() === 'success';

    if (!response.ok || !isServerSuccess) {
        throw new Error(payload.message || `Error HTTP ${response.status}`);
    }

    return payload;
}
