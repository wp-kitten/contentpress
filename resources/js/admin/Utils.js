/**
 * Helper class
 */
class Utils {

    constructor() {
        this.ajaxActions = {
        };
    }

    mapObject(object, callback) {
        return Object.keys( object ).map( function (key) {
            return callback( key, object[key] );
        } );
    }
}

export default new Utils();
