/*
    This evil format opens the users entire inbox as a format template in an iframe.

    Setting these HTTP headers does not help:

            Header add X-Frame-Options: "DENY"
                The srcdoc workaround bypasses this.

            Header add Access-Control-Allow-Origin "..."
                The client still can fetch and embed itself (same origin)
 */


window.onload = () => {
    const node = document.createElement("h2")
    node.innerText = document.querySelector("embedded-experience").content
    document.body.append(node)
}