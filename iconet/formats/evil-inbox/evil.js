window.onload = () => {
    const node = document.createElement("h2")
    node.innerText = document.querySelector("embedded-experience").content
    document.body.append(node)
}