import http from 'k6/http';

export let options = {
  vus: 1,
  iterations: 1,
};

const BASE_URL = 'http://localhost:8000/api/v1/orders/checkout?mode=unsafe';

const tokens = [
  'Y6e55H5xu37I4qOmRzVFahTcfSa6UiL5EsucxgWC3edd1342',
  'C1x5Y6MT4whni7Nmo91xu3uKrew3bnLVpgxQIgI477720a2d',
  'cVXhIofVzF01XmFA4qYBtQa1rfAiZ1RxbXupOFSXa5c8b71c',
  'GAmOCbmcYfesUsXyGOHtDjkAQl2IqCnm3q9pEfhVee256778',
  'GnFvtzCOJeoefHLTnPNl5EFL5mxDVGC5aXErWKi04b0ab83f'
];

export default function () {
  const requests = tokens.map(token => ({
    method: 'POST',
    url: BASE_URL,
    body: JSON.stringify({}),
    params: {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    },
  }));

  let responses = http.batch(requests);

  responses.forEach((res, i) => {
    console.log(`User ${i + 1} → ${res.status}`);
  });
}