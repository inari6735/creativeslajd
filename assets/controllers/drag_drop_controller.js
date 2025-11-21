import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        slideshowId: String
    }

    connect() {
        this.draggedElement = null;
    }

    dragStart(event) {
        this.draggedElement = event.currentTarget;
        this.draggedElement.classList.add('dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/html', event.currentTarget.innerHTML);
    }

    dragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';

        // Remove all drag-over classes first
        this.element.querySelectorAll('.slide-item').forEach(item => {
            item.classList.remove('drag-over');
        });

        const targetElement = event.target.closest('.slide-item');
        if (targetElement && targetElement !== this.draggedElement && this.draggedElement) {
            targetElement.classList.add('drag-over');
            
            const allSlides = Array.from(this.element.querySelectorAll('.slide-item'));
            const draggedIndex = allSlides.indexOf(this.draggedElement);
            const targetIndex = allSlides.indexOf(targetElement);
            
            if (draggedIndex < targetIndex) {
                targetElement.parentNode.insertBefore(this.draggedElement, targetElement.nextSibling);
            } else {
                targetElement.parentNode.insertBefore(this.draggedElement, targetElement);
            }
        }
    }

    drop(event) {
        event.stopPropagation();
        event.preventDefault();
        
        // Remove drag-over classes
        this.element.querySelectorAll('.slide-item').forEach(item => {
            item.classList.remove('drag-over');
        });
    }

    dragEnd(event) {
        if (this.draggedElement) {
            this.draggedElement.classList.remove('dragging');
            
            // Update slide numbers
            this.updateSlideNumbers();
            
            // Send new order to server
            this.saveOrder();
            
            this.draggedElement = null;
        }
    }

    updateSlideNumbers() {
        const slides = this.element.querySelectorAll('.slide-item');
        slides.forEach((slide, index) => {
            const numberSpan = slide.querySelector('.slide-number');
            if (numberSpan) {
                numberSpan.textContent = '#' + (index + 1);
            }
        });
    }

    saveOrder() {
        const slides = this.element.querySelectorAll('.slide-item');
        const order = [];

        slides.forEach((slide, index) => {
            order.push(parseInt(slide.dataset.slideId));
        });

        fetch(`/slide/reorder/${this.slideshowIdValue}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            // Order saved successfully
        })
        .catch(error => {
            console.error('Error saving order:', error);
        });
    }
}
