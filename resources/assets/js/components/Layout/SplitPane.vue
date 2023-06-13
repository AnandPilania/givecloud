
<template>
    <div class="GC-SplitPane" :class="[split, { 'GC-SplitPane--dragging': dragging }]" @mousemove="dragMove" @mouseup="dragEnd" @mouseleave="dragEnd">
        <div class="GC-SplitPane__Container">
            <div class="GC-SplitPane__Pane" :style="{ [property]: paneOneSize }">
                <slot name="one"></slot>
            </div>
            <div class="GC-SplitPane__Resizer" @mousedown="dragStart"></div>
            <div class="GC-SplitPane__Pane" :style="{ [property]: paneTwoSize }">
                <slot name="two"></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        min: {
            type: Number,
            default: 10
        },
        size: {
            type: Number,
            default: 25
        },
        split: {
            validator(value) {
                return ['vertical', 'horizontal'].indexOf(value) >= 0;
            },
            default: 'vertical'
        }
    },
    data() {
        return {
            percentage: Math.max(this.min, this.size),
            property: this.split === 'vertical' ? 'width' : 'height',
            dragging: false,
        };
    },
    computed: {
        paneOneSize: function() {
            return `calc(${this.percentage}% - 1px)`;
        },
        paneTwoSize: function() {
            return `calc(${100 - this.percentage}% - 1px)`;
        }
    },
    methods: {
        dragStart(e) {
            this.dragging = true;
            this.startingPageX = e.pageX;
            this.startingPercentage = this.percentage;
        },
        dragMove(e) {
            if (this.dragging) {
                const percentage = this.startingPercentage + ((e.pageX - this.startingPageX) / this.$el.offsetWidth * 100);
                this.percentage = Math.max(this.min, Math.min(100 - this.min, percentage));
            }
        },
        dragEnd() {
            this.dragging = false;
        }
    }
};
</script>

<style lang="scss">
.GC-SplitPane {
    &--dragging {
        cursor: col-resize;
    }

    &__Container {
        display: flex;
        flex-direction: row;
        height: 100%;
    }

    &__Pane,
    &__Resizer {
        height: 100%;
    }

    &__Resizer {
        background: #aaa;
        opacity: .4;
        z-index: 1;
        box-sizing: border-box;
        background-clip: padding-box;
        width: 11px;
        margin: 0 -5px;
        border-left: 5px solid rgba(0, 0, 0, 0);
        border-right: 5px solid rgba(0, 0, 0, 0);
        cursor: col-resize;
        &:hover,
        &:focus {
            border-left: 5px solid rgba(0, 0, 0, 0.5);
            border-right: 5px solid rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
    }
}
</style>
