const BASE_URL = "/api";

async function request(method, path, data = null) {
    const options = {
        method,
        headers: { "Content-Type": "application/json" },
    };

    if (data) options.body = JSON.stringify(data);

    const response = await fetch(`${BASE_URL}${path}`, options);

    if (response.status === 204) return null;

    return response.json();
}

export const api = {
    // Boards
    getBoards: () => request("GET", "/boards"),
    getBoard: (id) => request("GET", `/boards/${id}`),
    createBoard: (data) => request("POST", "/boards", data),
    updateBoard: (id, data) => request("PUT", `/boards/${id}`, data),
    deleteBoard: (id) => request("DELETE", `/boards/${id}`),

    // Columns
    createColumn: (data) => request("POST", "/columns", data),
    updateColumn: (id, data) => request("PUT", `/columns/${id}`, data),
    deleteColumn: (id) => request("DELETE", `/columns/${id}`),
    reorderColumns: (data) => request("POST", "/columns/reorder", data),

    // Cards
    createCard: (data) => request("POST", "/cards", data),
    updateCard: (id, data) => request("PUT", `/cards/${id}`, data),
    deleteCard: (id) => request("DELETE", `/cards/${id}`),
    moveCard: (id, data) => request("POST", `/cards/${id}/move`, data),
    reorderCards: (data) => request("POST", "/cards/reorder", data),

    // Checklist items
    createChecklistItem: (data) => request("POST", "/checklist-items", data),
    updateChecklistItem: (id, data) =>
        request("PUT", `/checklist-items/${id}`, data),
    deleteChecklistItem: (id) => request("DELETE", `/checklist-items/${id}`),
};
