import Endpoint from '@core/endpoint'

class ProductsEndpoint extends Endpoint {
  find(id) {
    return this.$http('GET', `products/${id}`).then((data) => {
      return Promise.resolve(data.product)
    })
  }
}

export default ProductsEndpoint
