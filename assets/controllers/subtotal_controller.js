import { Controller } from '@hotwired/stimulus';
const document = window.document;

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = ['subtotal'];
    outputElement = null;

    initialize() {
        this.outputElement = document.createElement('div');
        this.outputElement.className = 'subtotal';
        this.outputElement.textContent = 'Subtotal:';
        this.element.append(this.outputElement);
    }
    connect() {
        this.render();
    }
    render() {

        lineItems = document.getElementById("Sale_SaleLineItems");
        items = this.lineItems.getElementsByClassName("item").textContent;
        subtotal = 0;
        prices = [];
        this.items.array.forEach(element => {
            price = element.split('$');
            prices.push(price[1]);
        });
        this.subtotal = prices.reduce((accumulator, currentValue) => {
            return accumulator + currentValue
        },0);
        this.outputElement.innerHTML = subtotal;    
    }
}
