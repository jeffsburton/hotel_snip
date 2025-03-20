

function getTypeColor(typeId, transparency)
{
    let color = "rgba(";
    switch (Number(typeId))
    {
        case 1: color += "0, 0, 255, " + transparency; break;
        case 2: color += "255, 0, 0, " + transparency; break;
        case 3: color += "0, 255, 0, " + transparency; break;
        case 4: color += "255, 0, 255, " + transparency; break;
        case 5: color += "0, 255, 255, " + transparency; break;
        case 6: color += "200, 200, 0, " + transparency; break;
    }
    return color + ")";
}