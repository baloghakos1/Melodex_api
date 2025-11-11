| URL                                | HTTP Method | Auth | JSON Response               |
| ---------------------------------- | ----------- | ---- | --------------------------- |
| `/user/login`                      | POST        | ❌    | User's token                |
| `/users`                           | GET         | ✅    | All users                   |
| `/artists`                         | GET         | ❌    | All artists                 |
| `/artist`                          | POST        | ✅    | New artist added            |
| `/artist/{id}`                     | PATCH       | ✅    | Edited artist               |
| `/artist/{id}`                     | DELETE      | ✅    | Deletion successful         |
| `/artist/{id}/members`             | GET         | ❌    | All members of an artist    |
| `/artist/{id}/member`              | POST        | ✅    | New member added to artist  |
| `/artist/{artist_id}/member/{id}`  | PATCH       | ✅    | Edited member of the artist |
| `/artist/{artist_id}/members/{id}` | DELETE      | ✅    | Deletion successful         |
| `/artist/{id}/albums`              | GET         | ❌    | All albums of an artist     |
| `/artist/{id}/album`               | POST        | ✅    | New album added to artist   |
| `/artist/{artist_id}/album/{id}`   | PATCH       | ✅    | Edited album of the artist  |
| `/artist/{artist_id}/albums/{id}`  | DELETE      | ✅    | Deletion successful         |
| `/members`                         | GET         | ❌    | All members                 |
| `/member`                          | POST        | ✅    | New member added            |
| `/member/{id}`                     | PATCH       | ✅    | Edited member               |
| `/member/{id}`                     | DELETE      | ✅    | Deletion successful         |
| `/albums`                          | GET         | ❌    | All albums                  |
| `/album`                           | POST        | ✅    | New album added             |
| `/album/{id}`                      | PATCH       | ✅    | Edited album                |
| `/album/{id}`                      | DELETE      | ✅    | Deletion successful         |
| `/songs`                           | GET         | ❌    | All songs                   |
| `/song`                            | POST        | ✅    | New song added              |
| `/song/{id}`                       | PATCH       | ✅    | Edited song                 |
| `/song/{id}`                       | DELETE      | ✅    | Deletion successful         |
