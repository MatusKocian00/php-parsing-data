const headerClick = (id) => {
    console.log(id)

    for (let i = 0; i < 7; i++){
        if(i !== id) {
            document.querySelector("#col-eat-" + i).style.display = 'none';
            document.querySelector("#col-del-" + i).style.display = 'none';
            document.querySelector("#col-kol-" + i).style.display = 'none';
            document.querySelector("#col-koz-" + i).style.display = 'none';

        } else {
            document.querySelector("#col-eat-" + i).style.display = 'table-cell';
            document.querySelector("#col-del-" + i).style.display = 'table-cell';
            document.querySelector("#col-kol-" + i).style.display = 'table-cell';
            document.querySelector("#col-koz-" + i).style.display = 'table-cell';
        }
    }
}

const showAll = () =>{
    for (let i = 0; i < 7; i++){
        document.querySelector("#col-eat-" + i).style.display = 'table-cell';
        document.querySelector("#col-del-" + i).style.display = 'table-cell';
        document.querySelector("#col-kol-" + i).style.display = 'table-cell';
        document.querySelector("#col-koz-" + i).style.display = 'table-cell';

    }
}