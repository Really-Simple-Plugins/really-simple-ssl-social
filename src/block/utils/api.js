import axios from 'axios';

/**
 * Makes a get request to the desired post type and builds the query string based on an object.
 *
 * @param {int} post_id
 * @returns {AxiosPromise<any>}
 */
export const getButtons = (post_id) => {
    //domain.com/wp-json/complianz/v1/data/doctypes
    return axios.get(rsssl_social.site_url+`/wp-json/rsssl/v1/buttons/id/${post_id}`);
};