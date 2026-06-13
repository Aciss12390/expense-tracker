// Przechowujemy referencje do elementow DOM w jednym miejscu,
// aby dalsze funkcje nie musialy za kazdym razem wyszukiwac tych samych pol.
const form = document.getElementById('expenseForm');
const expenseId = document.getElementById('expenseId');
const title = document.getElementById('title');
const amount = document.getElementById('amount');
const category = document.getElementById('category');
const expenseDate = document.getElementById('expenseDate');
const note = document.getElementById('note');

const expensesList = document.getElementById('expensesList');
const totalAmount = document.getElementById('totalAmount');
const message = document.getElementById('message');
const submitBtn = document.getElementById('submitBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');

// Glowny start aplikacji: ustawiamy date, pobieramy dane i podpinamy zdarzenia.
document.addEventListener('DOMContentLoaded', () => {
    setTodayDate();
    loadExpenses();

    form.addEventListener('submit', handleFormSubmit);
    cancelEditBtn.addEventListener('click', cancelEdit);
});

function setTodayDate() {
    // Input type="date" oczekuje formatu RRRR-MM-DD.
    const today = new Date().toISOString().split('T')[0];
    expenseDate.value = today;
}

function showMessage(text) {
    message.textContent = text;

    // Krotkie komunikaty znikaja automatycznie, zeby nie zostawaly po kolejnej akcji.
    setTimeout(() => {
        message.textContent = '';
    }, 3000);
}

function getFormData() {
    // URLSearchParams przygotowuje dane w formacie odczytywanym przez PHP z $_POST.
    const data = new URLSearchParams();

    data.append('title', title.value.trim());
    data.append('amount', amount.value);
    data.append('category', category.value);
    data.append('expense_date', expenseDate.value);
    data.append('note', note.value.trim());

    if (expenseId.value) {
        data.append('id', expenseId.value);
    }

    return data;
}

function clearForm() {
    // Po zapisie lub anulowaniu wracamy do trybu dodawania nowego wydatku.
    form.reset();
    expenseId.value = '';
    submitBtn.textContent = 'Dodaj wydatek';
    cancelEditBtn.classList.add('hidden');
    setTodayDate();
}

function handleFormSubmit(event) {
    event.preventDefault();

    // Obecnosc ID oznacza edycje istniejacego rekordu; brak ID oznacza tworzenie nowego.
    const url = expenseId.value ? 'php/update.php' : 'php/create.php';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: getFormData()
    })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message);

            if (data.status === 'success') {
                clearForm();
                loadExpenses();
            }
        })
        .catch(error => {
            console.error('Błąd:', error);
            showMessage('Wystąpił błąd podczas zapisu danych');
        });
}

function loadExpenses() {
    // Lista i suma sa odswiezane po kazdej udanej zmianie w bazie.
    fetch('php/read.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                renderExpenses(result.data);
                totalAmount.textContent = `${parseFloat(result.total).toFixed(2)} zł`;
            }
        })
        .catch(error => {
            console.error('Błąd:', error);
            expensesList.innerHTML = '<div class="empty-state">Nie udało się pobrać danych.</div>';
        });
}

function renderExpenses(expenses) {
    expensesList.innerHTML = '';

    if (expenses.length === 0) {
        expensesList.innerHTML = '<div class="empty-state">Brak wydatków do wyświetlenia.</div>';
        return;
    }

    expenses.forEach(expense => {
        const card = document.createElement('article');
        card.className = 'expense-card';

        // Dane z bazy zabezpieczamy przed wstawieniem ich do HTML.
        const safeTitle = escapeHtml(expense.title);
        const safeCategory = escapeHtml(expense.category);
        const safeDate = escapeHtml(expense.expense_date);
        const safeNote = escapeHtml(expense.note ?? '');

        card.innerHTML = `
            <div>
                <h3>${safeTitle}</h3>
                <div class="expense-meta">
                    <span>Kategoria: ${safeCategory}</span>
                    <span>Data: ${safeDate}</span>
                </div>
                ${safeNote ? `<p class="expense-note">${safeNote}</p>` : ''}
            </div>

            <div>
                <div class="expense-amount">${parseFloat(expense.amount).toFixed(2)} zł</div>
                <div class="card-actions">
                    <button type="button" onclick="editExpense(${expense.id}, '${escapeText(expense.title)}', ${expense.amount}, '${escapeText(expense.category)}', '${expense.expense_date}', '${escapeText(expense.note ?? '')}')">
                        Edytuj
                    </button>
                    <button type="button" class="danger" onclick="deleteExpense(${expense.id})">
                        Usuń
                    </button>
                </div>
            </div>
        `;

        expensesList.appendChild(card);
    });
}

function editExpense(id, currentTitle, currentAmount, currentCategory, currentDate, currentNote) {
    // Wypelniamy formularz danymi z karty i przelaczamy przycisk na zapis zmian.
    expenseId.value = id;
    title.value = currentTitle;
    amount.value = currentAmount;
    category.value = currentCategory;
    expenseDate.value = currentDate;
    note.value = currentNote;

    submitBtn.textContent = 'Zapisz zmiany';
    cancelEditBtn.classList.remove('hidden');

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

function cancelEdit() {
    clearForm();
    showMessage('Edycja została anulowana');
}

function deleteExpense(id) {
    // Potwierdzenie chroni przed przypadkowym usunieciem rekordu.
    const confirmed = confirm('Czy na pewno chcesz usunąć ten wydatek?');

    if (!confirmed) {
        return;
    }

    const data = new URLSearchParams();
    data.append('id', id);

    fetch('php/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data
    })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message);

            if (data.status === 'success') {
                loadExpenses();
            }
        })
        .catch(error => {
            console.error('Błąd:', error);
            showMessage('Wystąpił błąd podczas usuwania danych');
        });
}

function escapeText(text) {
    // Tekst trafia do atrybutow onclick, wiec trzeba uciec znaki lamiace zapis JavaScript.
    return String(text)
        .replaceAll('\\', '\\\\')
        .replaceAll("'", "\\'")
        .replaceAll('"', '&quot;')
        .replaceAll('\n', ' ');
}

function escapeHtml(text) {
    // Tekst wyswietlany w HTML escapujemy, aby nie mogl zostac potraktowany jak kod strony.
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
