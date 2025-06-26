function filterTable() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let table = document.getElementById('dataTable');
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let rowText = '';
        for (let j = 0; j < td.length; j++) {
            rowText += td[j].textContent.toLowerCase();
        }
        tr[i].style.display = rowText.includes(input) ? '' : 'none';
    }
}