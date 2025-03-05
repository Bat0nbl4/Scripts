function ShowHide(first, second) {
    obj1 = document.getElementById(first)
    obj2 = document.getElementById(second)
    if (obj1.style.display === 'flex') {
        obj1.style.display = 'none'
        obj2.style.display = 'flex'
    } else {
        obj1.style.display = 'flex'
        obj2.style.display = 'none'
    }
}
