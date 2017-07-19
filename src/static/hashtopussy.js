function expansionCheck(elementId){
    var isExpanded = $(elementId).attr("aria-expanded");
    if(isExpanded){
        alert("Expanded!");
    }
}

