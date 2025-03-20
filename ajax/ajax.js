/**
 *
 * @param path The path to the ajax php file. The 'ajax' directory is automatically prepended.
 * @param params An array of http parameters. Each element is an object with the name and value (e.g. {name: value})
 * @returns {Promise<* | void>} The promise resolves to the data element of the json returned from the call.
 */
async function hotelAjax(path, params = [])
{

    // get the types.
    var url = "ajax/" + path; //+ (path.indexOf("?") == -1 ? "?" : "&") + "PHPSESSID=" + gSession;
    console.log("ajax: ", url, params);

    // construct the data
    const formData = new FormData();
    params.forEach(param => {
        for (let key in param)
            formData.append(key, param[key]);
    });

    return fetch(url, {method: 'POST', headers: {accept: 'application/json'}, body: formData})
        .then(response => {
            // Ensure the response is in text format to match the original functionality
            if (!response.ok) {
                console.log(response);
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            if (result.success){
                console.log("Success: ", result);
                return result.data;
            }
            else
                console.log("Error: ", result);
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
}