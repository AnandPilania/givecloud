class Deferred {
  constructor(customPromise) {
    customPromise = customPromise || Promise
    this.promise = new customPromise((resolve, reject) => {
      this.resolve = resolve
      this.reject = reject
    })

    this.then = this.promise.then.bind(this.promise)
    this.catch = this.promise.catch.bind(this.promise)
    this.finally = this.promise.finally.bind(this.promise)
  }
}

export default Deferred
